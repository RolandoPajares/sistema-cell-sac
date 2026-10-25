<details>
    <summary>Datos</summary>
    <pre>{{ json_encode(['antes' => $log->valores_anteriores, 'despues' => $log->valores_nuevos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</details>
