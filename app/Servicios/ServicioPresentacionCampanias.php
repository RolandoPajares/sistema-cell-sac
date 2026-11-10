<?php

namespace App\Servicios;

use Illuminate\Support\Collection;

final class ServicioPresentacionCampanias
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function preparar(array $data): array
    {
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
        $campaignRecords = method_exists($campaigns, 'getCollection')
            ? $campaigns->getCollection()
            : collect($campaigns);
        $allCampaigns = $campaignRecords
            ->concat($data['topCampaigns'] ?? [])
            ->filter(static fn (mixed $campaign): bool => is_object($campaign))
            ->unique('id');
        $data['campaignStatuses'] = $this->statuses($campaignRecords);
        $data['campaignImages'] = $this->images($allCampaigns);
        $data['campaignSegmentIds'] = $this->segmentIds($campaignRecords);
        $data['viewsTrendLabel'] = $this->trendLabel($data['viewsTrend'] ?? null);
        $data['clicksTrendLabel'] = $this->trendLabel($data['clicksTrend'] ?? null);
        $data['distributionTotal'] = max(1, (int) $this->sum($data['distribution'] ?? []));
        $firstDistribution = collect($data['distribution'] ?? [])->first();
        $data['distributionAngle'] = min(360, (($firstDistribution?->total ?? 0) / $data['distributionTotal']) * 360);
        $data['perPageOptions'] = [10, 20, 50];
        $data['paginationQuery'] = request()->except(['page', 'por_pagina']);

        return $data;
    }

    /** @param iterable<int, object> $campaigns */
    private function statuses(iterable $campaigns): array
    {
        $statuses = [];

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
    private function images(iterable $campaigns): array
    {
        $images = [];

        foreach ($campaigns as $campaign) {
            $path = $campaign->url_imagen;
            $images[$campaign->id] = $path
                ? (str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/')))
                : null;
        }

        return $images;
    }

    /** @param iterable<int, object> $campaigns */
    private function segmentIds(iterable $campaigns): array
    {
        $segmentIds = [];

        foreach ($campaigns as $campaign) {
            $segmentIds[$campaign->id] = $campaign->segments->modelKeys();
        }

        return $segmentIds;
    }

    private function trendLabel(?float $trend): string
    {
        return $trend === null
            ? 'Sin periodo anterior'
            : ($trend >= 0 ? '+' : '').number_format($trend, 1).'% vs. mes anterior';
    }

    private function sum(iterable $values): float|int
    {
        return $values instanceof Collection ? $values->sum('total') : collect($values)->sum('total');
    }
}
