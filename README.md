
# Construcción de informacion

Invetario, para tener los provedores 




| Campo              | Tipo          | Descripción                                                 |

| ------------------ | ------------- | ----------------------------------------------------------- |

| `id`               | INT           | Identificador interno (no se muestra al usuario)            |

| `producto`         | VARCHAR(255)  | Nombre completo del producto (incluye las especificaciones) |

| `cantidad`         | DECIMAL(10,2) | Cantidad cotizada                                           |

| `unidad_medida`    | VARCHAR(20)   | kg, m², galón, unidad, etc.                                 |

| `precio`           | DECIMAL(12,2) | Precio cotizado                                             |

| `proveedor`        | VARCHAR(150)  | Nombre del proveedor                                        |

| `fecha_cotizacion` | DATE          | Fecha de la cotización                                      |
