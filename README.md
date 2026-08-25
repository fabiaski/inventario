
# Construcción de informacion

Invetario, para tener los provedores 




| Campo              | Tipo          | Descripción                                                 |


| `id`               | INT           | Identificador interno (no se muestra al usuario)            |

| `producto`         | VARCHAR(255)  | Nombre completo del producto (incluye las especificaciones) |

| `cantidad`         | DECIMAL(10,2) | Cantidad cotizada                                           |

| `unidad_medida`    | VARCHAR(20)   | kg, m², galón, unidad, etc.                                 |

| `precio`           | DECIMAL(12,2) | Precio cotizado                                             |

| `proveedor`        | VARCHAR(150)  | Nombre del proveedor                                        |

| `fecha_cotizacion` | DATE          | Fecha de la cotización                                      |

# para gregar pagina

<?php include __DIR__ . '/../../includes/header.php'; ?>

<?php include __DIR__ . '/../../includes/sidebar.php'; ?>

<div class="admin-main">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Aquí va el formulario -->

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

</div>

<?php include __DIR__ . '/../../includes/scripts.php'; ?>




Composer

https://getcomposer.org/Composer-Setup.exe?utm_source=chatgpt.com

Composer --version


composer require dompdf/dompdf

composer require phpoffice/phpspreadsheet☺


1. Abre php.ini

En XAMPP:

XAMPP Control Panel → Apache → Config → PHP (php.ini)

2. Busca esta línea

Presiona Ctrl + F y busca:

;extension=zip

Si está así:

;extension=zip

quita el ;:

extension=zip


3. Guarda el archivo

Guarda php.ini.

4. Reinicia Apache

En XAMPP:

Stop Apache
↓
Start Apache

Es importante reiniciarlo porque PHP carga las extensiones cuando inicia Apache.





Haz solamente estos 4 pasos ahora

Paso 1:

ngrok version

Paso 2:

ngrok config add-authtoken "TU_TOKEN"

Paso 3:

ngrok http 80

Paso 4: copia la línea que dice:

Forwarding    https://........ngrok.app -> http://localhost:80



https://dashboard.ngrok.com/get-started/setup/windows
















1. Crear la tarea

Abre CMD como administrador y ejecuta:

schtasks /create /tn "Inventario - Alertas WhatsApp" /tr "\"C:\xampp\php\php.exe\" \"C:\xampp\htdocs\inventario\src\pages\procesos\alertas_whatsapp.php\"" /sc daily /st 08:00 /f

Debería aparecer:

Correcto: se creó correctamente la tarea programada
"Inventario - Alertas WhatsApp".
2. Probarla inmediatamente

Sin esperar a las 8:00 a. m.:

schtasks /run /tn "Inventario - Alertas WhatsApp"

Debe aparecer:

CORRECTO: se ha intentado ejecutar la tarea programada
"Inventario - Alertas WhatsApp".
3. Comprobar que se ejecutó

Revisa:

C:\xampp\htdocs\inventario\src\pages\procesos\alertas\alertas_debug.txt

Si aparece algo como:

HTTP: 200
RESPUESTA META:
{"messaging_product":"whatsapp"...}

RESULTADO: ALERTA ENVIADA CORRECTAMENTE