<?php

namespace App\Servicios\Gestion\EcommerceMarketing\Campanas;

use Illuminate\Support\Collection;

final class ServicioPresentacionCampanias
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function preparar(array $data): array
    {
        // Convierte los datos de campanas en valores listos para la vista.
        // Guarda en $campaigns el resultado necesario para continuar la operacion.
        $campaigns = $data['campaigns'] ?? collect();
        $data['locationLabels'] = [
            'emergente' => 'Emergente',
            'lateral' => 'Lateral',
            'barra_superior' => 'Barra superior',
        ];
        $data['campaignDestinations'] = [
            'catalog' => 'Catálogo',
            'catalog.index' => 'Catálogo público',
            'products' => 'Productos',
            'offers' => 'Ofertas',
        ];
        // Guarda en $campaignRecords el resultado necesario para continuar la operacion.
        $campaignRecords = method_exists($campaigns, 'getCollection')
            ? $campaigns->getCollection()
            : collect($campaigns);
        // Guarda en $allCampaigns el resultado necesario para continuar la operacion.
        $allCampaigns = $campaignRecords
            ->concat($data['topCampaigns'] ?? [])
            ->filter(static fn (mixed $campaign): bool => is_object($campaign))
            ->unique('id');
        $data['campaignStatuses'] = $this->estados($campaignRecords);
        $data['campaignImages'] = $this->imagenes($allCampaigns);
        $data['campaignSegmentIds'] = $this->idsSegmentos($campaignRecords);
        $data['viewsTrendLabel'] = $this->etiquetaTendencia($data['viewsTrend'] ?? null);
        $data['clicksTrendLabel'] = $this->etiquetaTendencia($data['clicksTrend'] ?? null);
        $data['distributionTotal'] = max(1, (int) $this->suma($data['distribution'] ?? []));
        // Guarda en $firstDistribution el resultado de la consulta ejecutada.
        $firstDistribution = collect($data['distribution'] ?? [])->first();
        $data['distributionAngle'] = min(360, (($firstDistribution?->total ?? 0) / $data['distributionTotal']) * 360);
        $data['perPageOptions'] = [10, 20, 50];
        $data['paginationQuery'] = request()->except(['page', 'por_pagina']);

        return $data;
    }

    /** @param iterable<int, object> $campaigns */
    private function estados(iterable $campaigns): array
    {
        // Calcula el estado visual de cada campana.
        // Agrupa en $statuses los datos que se utilizaran en el siguiente paso.
        $statuses = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($campaigns as $campaign) {
            $statuses[$campaign->id] = ! $campaign->activo
                ? ['label' => 'Pausada', 'class' => 'paused']
                : ($campaign->finaliza_en?->isPast()
                    ? ['label' => 'Finalizada', 'class' => 'finished']
                    : ($campaign->inicia_en?->isFuture()
                        ? ['label' => 'Programada', 'class' => 'scheduled']
                        : ['label' => 'Activa', 'class' => 'active']));
        }

        return $statuses;
    }

    /** @param iterable<int, object> $campaigns */
    private function imagenes(iterable $campaigns): array
    {
        // Resuelve las imagenes publicas asociadas a las campanas.
        // Agrupa en $images los datos que se utilizaran en el siguiente paso.
        $images = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($campaigns as $campaign) {
            // Guarda en $path el resultado necesario para continuar la operacion.
            $path = $campaign->url_imagen;
            $images[$campaign->id] = $path
                ? (str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/')))
                : null;
        }

        return $images;
    }

    /** @param iterable<int, object> $campaigns */
    private function idsSegmentos(iterable $campaigns): array
    {
        // Extrae los identificadores de segmentos usados por las campanas.
        // Agrupa en $segmentIds los datos que se utilizaran en el siguiente paso.
        $segmentIds = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($campaigns as $campaign) {
            $segmentIds[$campaign->id] = $campaign->segments->modelKeys();
        }

        return $segmentIds;
    }

    private function etiquetaTendencia(?float $trend): string
    {
        // Traduce la variacion numerica a una etiqueta comprensible.
        return $trend === null
            ? 'Sin periodo anterior'
            : ($trend >= 0 ? '+' : '').number_format($trend, 1).'% vs. mes anterior';
    }

    private function suma(iterable $values): float|int
    {
        // Acumula valores numericos ignorando entradas no validas.
        return $values instanceof Collection ? $values->sum('total') : collect($values)->sum('total');
    }
}
