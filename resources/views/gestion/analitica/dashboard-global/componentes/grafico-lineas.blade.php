
{{-- Componente de analitica: dashboard-global\componentes\grafico-lineas.blade.php. Renderiza un bloque visual reutilizable. --}}
{{-- Bloque visual: dibuja la serie de líneas con los puntos entregados por el controlador. --}}
<div class="tc-dg__line-chart" role="img" aria-label="{{ $descripcion }}">
    <div class="tc-dg__line-axis"><span>S/ 25,000</span><span>S/ 20,000</span><span>S/ 15,000</span><span>S/ 10,000</span><span>S/ 5,000</span><span>S/ 0</span></div>
    <svg viewBox="0 0 900 210" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="tc-dg-line-fill-{{ $identificador }}" x1="0" x2="0" y1="0" y2="1">
                <stop offset="0" stop-color="#0b73ff" stop-opacity=".24" />
                <stop offset="1" stop-color="#0b73ff" stop-opacity=".02" />
            </linearGradient>
        </defs>
        <path class="tc-dg__line-area" d="M0 150 L45 153 L90 139 L135 145 L180 131 L225 136 L270 115 L315 127 L360 113 L405 119 L450 94 L495 110 L540 98 L585 95 L630 97 L675 68 L720 72 L765 48 L810 63 L855 37 L900 30 L900 210 L0 210 Z" fill="url(#tc-dg-line-fill-{{ $identificador }})" />
        <polyline class="tc-dg__line-primary" points="0,150 45,153 90,139 135,145 180,131 225,136 270,115 315,127 360,113 405,119 450,94 495,110 540,98 585,95 630,97 675,68 720,72 765,48 810,63 855,37 900,30" />
        <polyline class="tc-dg__line-secondary" points="0,175 45,177 90,165 135,168 180,160 225,163 270,146 315,154 360,141 405,146 450,131 495,138 540,126 585,127 630,130 675,106 720,113 765,98 810,107 855,88 900,84" />
    </svg>
    <div class="tc-dg__line-labels"><span>27 ago</span><span>2 sep</span><span>8 sep</span><span>14 sep</span><span>20 sep</span><span>26 sep</span></div>
</div>
