
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `alertas_whatsapp` (
  `id` int(11) NOT NULL,
  `proceso_id` int(11) NOT NULL,
  `dias_restantes` int(11) NOT NULL,
  `fecha_alerta` date NOT NULL,
  `estado` enum('enviada','error') NOT NULL DEFAULT 'error',
  `whatsapp_message_id` varchar(255) DEFAULT NULL,
  `respuesta_meta` text DEFAULT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `numero_contrato` varchar(100) NOT NULL,
  `objeto_contrato` text NOT NULL,
  `valor_contrato` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contratos_smlmv` (
  `id` int(11) NOT NULL,
  `numero_contrato` varchar(100) NOT NULL,
  `entidad` varchar(255) NOT NULL,
  `objeto` text NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `anio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `cliente` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `porcentaje_retencion` decimal(8,5) DEFAULT 0.00000,
  `aplica_pago1` tinyint(1) DEFAULT 0,
  `aplica_pago2` tinyint(1) DEFAULT 0,
  `porcentaje_ganancia_ideal` decimal(8,5) DEFAULT 20.00000,
  `total_venta` decimal(12,2) DEFAULT 0.00,
  `valor_retencion` decimal(12,2) DEFAULT 0.00,
  `valor_pagos` decimal(12,2) DEFAULT 0.00,
  `llega` decimal(12,2) DEFAULT 0.00,
  `ganancia` decimal(12,2) DEFAULT 0.00,
  `ganancia_ideal` decimal(12,2) DEFAULT 0.00,
  `diferencia` decimal(12,2) DEFAULT 0.00,
  `estado` enum('Borrador','Finalizada') DEFAULT 'Borrador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `detalle_cotizacion` (
  `id` int(11) NOT NULL,
  `cotizacion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `valor_unidad` decimal(10,2) NOT NULL,
  `porcentaje_incremento` decimal(8,5) DEFAULT 0.00000,
  `valor_incremento` decimal(10,2) NOT NULL,
  `valor_unidad_incremento` decimal(10,2) NOT NULL,
  `valor_total_unidad` decimal(10,2) NOT NULL,
  `total_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `proveedor` varchar(150) NOT NULL,
  `numero_factura` varchar(100) NOT NULL,
  `valor` decimal(15,2) NOT NULL DEFAULT 0.00,
  `valor_sin_iva` decimal(15,2) NOT NULL DEFAULT 0.00,
  `porcentaje_iva` int(11) NOT NULL DEFAULT 19,
  `valor_iva` decimal(15,2) NOT NULL DEFAULT 0.00,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `procesos` (
  `id` int(11) NOT NULL,
  `nombre_contrato` varchar(255) NOT NULL,
  `fecha_entrega` date NOT NULL,
  `estado` enum('proceso','finalizado') NOT NULL DEFAULT 'proceso',
  `fecha_finalizacion` datetime DEFAULT NULL,
  `ultima_alerta_whatsapp` datetime DEFAULT NULL,
  `alerta_3_dias_enviada` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `proceso_productos` (
  `id` int(11) NOT NULL,
  `proceso_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `comprado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_compra` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `producto` varchar(255) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `fecha_cotizacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `salarios_minimos` (
  `id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `salario` decimal(12,2) NOT NULL,
  `fecha_registro` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `soportes_factura` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
ALTER TABLE `alertas_whatsapp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alerta_unica` (`proceso_id`,`dias_restantes`,`fecha_alerta`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contratos_smlmv`
--
ALTER TABLE `contratos_smlmv`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrato_id` (`contrato_id`);

--
-- Indices de la tabla `procesos`
--
ALTER TABLE `procesos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proceso_productos`
--
ALTER TABLE `proceso_productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proceso_producto` (`proceso_id`,`producto_id`),
  ADD KEY `fk_proceso_productos_producto` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `salarios_minimos`
--
ALTER TABLE `salarios_minimos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_salario_anio` (`anio`);

--
-- Indices de la tabla `soportes_factura`
--
ALTER TABLE `soportes_factura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_whatsapp`
--
ALTER TABLE `alertas_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `contratos_smlmv`
--
ALTER TABLE `contratos_smlmv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `procesos`
--
ALTER TABLE `procesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `proceso_productos`
--
ALTER TABLE `proceso_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=851;

--
-- AUTO_INCREMENT de la tabla `salarios_minimos`
--
ALTER TABLE `salarios_minimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `soportes_factura`
--
ALTER TABLE `soportes_factura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `detalle_cotizacion_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `detalle_cotizacion_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proceso_productos`
--
ALTER TABLE `proceso_productos`
  ADD CONSTRAINT `fk_proceso_productos_proceso` FOREIGN KEY (`proceso_id`) REFERENCES `procesos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_proceso_productos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soportes_factura`
--
ALTER TABLE `soportes_factura`
  ADD CONSTRAINT `soportes_factura_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
