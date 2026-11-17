# Sistema Cell SAC

Sistema web de gestión comercial e inventario para una empresa de tecnología y venta de equipos celulares.

## Funcionalidades principales

- Autenticación y registro de usuarios.
- Control de acceso mediante roles, permisos y alcance por empresa/sede.
- Gestión de categorías, marcas, productos, variantes e imágenes.
- Control de existencias, ajustes, movimientos y transferencias entre almacenes.
- Catálogo público, carrito, checkout y seguimiento de pedidos.
- Gestión de compras, cotizaciones, ventas, clientes y proveedores.
- Paneles, reportes, exportación tabular y auditoría de operaciones.
- Módulos financieros para cajas, pagos, cuentas por cobrar/pagar y reembolsos.

## Requisitos

- PHP 8.5 o compatible con `composer.json`.
- Composer.
- Node.js y npm.
- MariaDB 10.4 o compatible.
- Extensiones PHP `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` y `zip`.

## Instalación local

1. Instalar dependencias:

   ```bash
   composer install
   npm install
   ```

2. Crear el archivo de entorno y generar la clave:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. Configurar en `.env` la conexión MariaDB:

   ```dotenv
   APP_NAME="CELL SAC"
   APP_URL=http://localhost/sistema-cell-sac/public
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bd_cell
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

4. Crear la base de datos e importar el esquema incluido:

   ```bash
   mysql -h 127.0.0.1 -P 3306 -u tu_usuario -p bd_cell < "storage/md_tecnologia_digital_cell (1).sql"
   ```

   También se puede importar el archivo desde phpMyAdmin. El archivo contiene el esquema MariaDB del sistema y no incluye contraseñas ni datos personales de usuarios. Esta entrega usa ese esquema base versionado; la importación del SQL es el paso que crea las tablas.

5. Inicializar la empresa y el administrador desde la consola:

   ```bash
   php artisan technology-cell:inicializar --vista-previa
   php artisan technology-cell:inicializar --confirmar
   ```

   El comando solicita los datos de la empresa, sede, almacén y administrador. La contraseña debe tener al menos 12 caracteres, letras y números.

6. Construir los recursos frontend:

   ```bash
   npm run build
   ```

7. Ejecutar la aplicación con Apache/XAMPP o con el servidor de desarrollo:

   ```bash
   php artisan serve
   ```

## Pruebas

Las pruebas usan SQLite en memoria. En Windows, si el entorno tiene una variable `DB_CONNECTION` externa, ejecutar:

```powershell
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=':memory:'
$env:DB_URL=''
php artisan test --compact
```

Resultado de verificación del entregable: 297 pruebas ejecutadas, 279 aprobadas y 18 omitidas porque requieren una instancia MariaDB descartable independiente.

## Flujo recomendado para la demostración

1. Iniciar sesión con el administrador creado.
2. Mostrar el dashboard y el menú dinámico según permisos.
3. Registrar o revisar una categoría y un producto.
4. Mostrar que la categoría cuenta sus productos mediante la relación `categoria_id`.
5. Ajustar existencias y revisar el movimiento de inventario.
6. Abrir el catálogo público, agregar un producto al carrito y completar el checkout.
7. Volver al panel para consultar el pedido y actualizar su estado.
8. Mostrar reportes, exportación y registro de auditoría.

## Comandos útiles

```bash
php artisan route:list
php artisan technology-cell:inicializar-rbac --vista-previa
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
```

## Observaciones de entrega

- No subir el archivo `.env` al repositorio.
- Cambiar `APP_DEBUG=false` en cualquier entorno que no sea desarrollo local.
- No reutilizar contraseñas de desarrollo en la presentación.
- Las integraciones externas como SUNAT requieren credenciales y autorización propias; el resto del flujo interno funciona con la base MariaDB del proyecto.
