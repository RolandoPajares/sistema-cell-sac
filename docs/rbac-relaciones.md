# Relaciones del RBAC

```mermaid
erDiagram
    roles ||--o{ roles_permisos : concede
    permisos ||--o{ roles_permisos : integra
    modulos ||--o{ pantallas : contiene
    modulos ||--o{ permisos : clasifica
    pantallas ||--o{ permisos : expone
    roles ||--o{ usuarios_roles : asigna
    usuarios ||--o{ usuarios_roles : recibe
    usuarios ||--o{ usuarios_sedes_roles : recibe
    sedes ||--o{ usuarios_sedes_roles : delimita
    roles ||--o{ usuarios_sedes_roles : asigna
    roles ||--o{ roles_menus : dispone
    menus ||--o{ roles_menus : publica
    menus ||--o{ elementos_menu : contiene
    elementos_menu ||--o{ elementos_menu : anida
    pantallas ||--o{ elementos_menu : enlaza
    permisos ||--o{ elementos_menu : protege
```

El ámbito (`empresa`, `sede` o `propio`) vive en la relación `roles_permisos` y en la asignación por sede; no se replica en columnas por módulo ni por rol. La matriz administra únicamente relaciones existentes y el servidor vuelve a validar autorización, alcance, concurrencia y conservación del último acceso administrativo global.
