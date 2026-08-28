<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


//==================================================
// VALIDAR ID
//==================================================

$cotizacionId = (int) ($_GET['id'] ?? 0);

if ($cotizacionId <= 0) {
    exit('Cotización no válida.');
}


//==================================================
// BUSCAR COTIZACIÓN
//==================================================

$sqlCotizacion = "
    SELECT
        id,
        porcentaje_retencion,
        aplica_pago1,
        aplica_pago2,
        porcentaje_ganancia_ideal,
        total_venta,
        valor_retencion,
        valor_pagos,
        llega,
        ganancia,
        ganancia_ideal,
        diferencia,
        estado
    FROM cotizaciones
    WHERE id = ?
";

$stmtCotizacion = $conexion->prepare($sqlCotizacion);

if (!$stmtCotizacion) {
    exit('Error preparando la consulta de cotización.');
}

$stmtCotizacion->bind_param(
    "i",
    $cotizacionId
);

$stmtCotizacion->execute();

$resultadoCotizacion = $stmtCotizacion->get_result();

$cotizacion = $resultadoCotizacion->fetch_assoc();

$stmtCotizacion->close();


if (!$cotizacion) {
    exit('La cotización no existe.');
}


//==================================================
// VERIFICAR ESTADO
//==================================================

if ($cotizacion['estado'] !== 'Finalizada') {
    exit('La cotización todavía no está finalizada.');
}


//==================================================
// BUSCAR PRODUCTOS
//==================================================

$sqlProductos = "
    SELECT
        dc.cantidad,
        dc.valor_unidad,
        dc.porcentaje_incremento,
        dc.valor_incremento,
        dc.valor_unidad_incremento,
        dc.valor_total_unidad,
        dc.total_venta,
        p.producto,
        p.unidad_medida
    FROM detalle_cotizacion dc

    INNER JOIN productos p
        ON p.id = dc.producto_id

    WHERE dc.cotizacion_id = ?

    ORDER BY dc.id ASC
";

$stmtProductos = $conexion->prepare($sqlProductos);

if (!$stmtProductos) {
    exit('Error preparando la consulta de productos.');
}

$stmtProductos->bind_param(
    "i",
    $cotizacionId
);

$stmtProductos->execute();

$resultadoProductos = $stmtProductos->get_result();

$productos = [];

while ($fila = $resultadoProductos->fetch_assoc()) {
    $productos[] = $fila;
}

$stmtProductos->close();


//==================================================
// CREAR EXCEL
//==================================================

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Cotización');



//==================================================
// ENCABEZADOS
//==================================================

$encabezados = [
    '#',
    'Producto',
    'Cantidad',
    'UND',
    'Valor UND',
    'Total UND',
    '% Inc.',
    'Valor Inc.',
    'UND + Inc.',
    'Total Venta'
];

$columnas = [
    'A',
    'B',
    'C',
    'D',
    'E',
    'F',
    'G',
    'H',
    'I',
    'J'
];


$filaEncabezado = 3;

foreach ($encabezados as $indice => $encabezado) {

    $celda = $columnas[$indice] . $filaEncabezado;

    $sheet->setCellValue(
        $celda,
        $encabezado
    );
}


//==================================================
// ESTILO ENCABEZADOS
//==================================================

$sheet->getStyle('A3:J3')->applyFromArray([

    'font' => [
        'bold' => true
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAD3'
        ]
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],

    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]

]);


//==================================================
// PRODUCTOS
//==================================================

$fila = 4;

foreach ($productos as $indice => $producto) {

    $sheet->setCellValue(
        'A' . $fila,
        $indice + 1
    );

    $sheet->setCellValue(
        'B' . $fila,
        $producto['producto']
    );

    $sheet->setCellValue(
        'C' . $fila,
        (float) $producto['cantidad']
    );

    $sheet->setCellValue(
        'D' . $fila,
        $producto['unidad_medida']
    );

    $sheet->setCellValue(
        'E' . $fila,
        (float) $producto['valor_unidad']
    );

    $sheet->setCellValue(
        'F' . $fila,
        (float) $producto['valor_total_unidad']
    );

    $sheet->setCellValue(
        'G' . $fila,
        (float) $producto['porcentaje_incremento']
    );

    $sheet->setCellValue(
        'H' . $fila,
        (float) $producto['valor_incremento']
    );

    $sheet->setCellValue(
        'I' . $fila,
        (float) $producto['valor_unidad_incremento']
    );

    $sheet->setCellValue(
        'J' . $fila,
        (float) $producto['total_venta']
    );


    // Bordes

    $sheet->getStyle(
        'A' . $fila . ':J' . $fila
    )->applyFromArray([

        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]

    ]);


    $fila++;
}


//==================================================
// FORMATOS NUMÉRICOS
//==================================================

if ($fila > 4) {

    // Cantidad
    $sheet->getStyle(
        'C4:C' . ($fila - 1)
    )->getNumberFormat()
      ->setFormatCode('#,##0');

    // Porcentajes
    $sheet->getStyle(
        'G4:G' . ($fila - 1)
    )->getNumberFormat()
      ->setFormatCode('0.#####');


    // Valores monetarios
    $sheet->getStyle(
        'E4:F' . ($fila - 1)
    )->getNumberFormat()
      ->setFormatCode('$ #,##0');

    $sheet->getStyle(
        'H4:J' . ($fila - 1)
    )->getNumberFormat()
      ->setFormatCode('$ #,##0');

}


//==================================================
// RESUMEN
//==================================================

$filaResumen = $fila + 2;


$sheet->setCellValue(
    'A' . $filaResumen,
    'RESUMEN'
);

$sheet->mergeCells(
    'A' . $filaResumen . ':B' . $filaResumen
);


$sheet->getStyle(
    'A' . $filaResumen . ':B' . $filaResumen
)->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 13
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAD3'
        ]
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],

    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]

]);


//==================================================
// DATOS DEL RESUMEN
//==================================================

$filaResumen++;


// Total Venta

$sheet->setCellValue(
    'A' . $filaResumen,
    'Total Venta'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['total_venta']
);

$filaResumen++;


// Retención

$sheet->setCellValue(
    'A' . $filaResumen,
    'Retención (' .
    rtrim(
        rtrim(
            number_format(
                (float) $cotizacion['porcentaje_retencion'],
                5,
                '.',
                ''
            ),
            '0'
        ),
        '.'
    )
    . '%)'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['valor_retencion']
);

$filaResumen++;


// Valor Pagos

$sheet->setCellValue(
    'A' . $filaResumen,
    'Valor Pagos'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['valor_pagos']
);

$filaResumen++;


//==================================================
// RESULTADO FINAL
//==================================================

$sheet->setCellValue(
    'A' . $filaResumen,
    'RESULTADO FINAL'
);

$sheet->mergeCells(
    'A' . $filaResumen . ':B' . $filaResumen
);

$sheet->getStyle(
    'A' . $filaResumen . ':B' . $filaResumen
)->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 13
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAD3'
        ]
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],

    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]

]);

$filaResumen++;



// Valor total unidad

$valorTotalUnidad = 0;

foreach ($productos as $producto) {

    $valorTotalUnidad +=
        (float) $producto['valor_total_unidad'];

}

$sheet->setCellValue(
    'A' . $filaResumen,
    'Valor total unidad'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    $valorTotalUnidad
);

$filaResumen++;


// Llega

$sheet->setCellValue(
    'A' . $filaResumen,
    'Llega'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['llega']
);

$filaResumen++;


// Ganancia

$sheet->setCellValue(
    'A' . $filaResumen,
    'Ganancia'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['ganancia']
);

$filaResumen++;


// Ganancia Ideal

$porcentajeGananciaIdeal =
    (float) $cotizacion['porcentaje_ganancia_ideal'];

$sheet->setCellValue(
    'A' . $filaResumen,
    'Ganancia Ideal ' .
    rtrim(
        rtrim(
            number_format(
                $porcentajeGananciaIdeal,
                5,
                '.',
                ''
            ),
            '0'
        ),
        '.'
    )
    . '%'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['ganancia_ideal']
);

$filaResumen++;


// Diferencia

$sheet->setCellValue(
    'A' . $filaResumen,
    'Diferencia'
);

$sheet->setCellValue(
    'B' . $filaResumen,
    (float) $cotizacion['diferencia']
);


//==================================================
// ESTILO RESUMEN
//==================================================

$sheet->getStyle(
    'A' . ($fila - 1) . ':B' . $filaResumen
)->applyFromArray([

    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]

]);


//==================================================
// FORMATO MONETARIO RESUMEN
//==================================================

$sheet->getStyle(
    'B' . ($fila - 1) . ':B' . $filaResumen
)->getNumberFormat()
  ->setFormatCode('$ #,##0');


//==================================================
// NEGRITA PARA CONCEPTOS
//==================================================

$sheet->getStyle(
    'A' . ($fila - 1) . ':A' . $filaResumen
)->getFont()->setBold(true);


//==================================================
// ALINEACIÓN
//==================================================

$sheet->getStyle(
    'A3:J' . ($fila - 1)
)->getAlignment()->setVertical(
    Alignment::VERTICAL_CENTER
);

$sheet->getStyle(
    'A3:A' . ($fila - 1)
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

$sheet->getStyle(
    'C3:C' . ($fila - 1)
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

$sheet->getStyle(
    'D3:D' . ($fila - 1)
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

$sheet->getStyle(
    'G3:G' . ($fila - 1)
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);


//==================================================
// ANCHO DE COLUMNAS
//==================================================

$sheet->getColumnDimension('A')->setWidth(17);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(8);
$sheet->getColumnDimension('D')->setWidth(11);
$sheet->getColumnDimension('E')->setWidth(11);
$sheet->getColumnDimension('F')->setWidth(13);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('H')->setWidth(14);
$sheet->getColumnDimension('I')->setWidth(14);
$sheet->getColumnDimension('J')->setWidth(14);


//==================================================
// CONGELAR ENCABEZADOS
//==================================================

//$sheet->freezePane('A4');


//==================================================
// CONFIGURAR DESCARGA
//==================================================

$nombreArchivo =
    'cotizacion_' . $cotizacionId . '.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo .
    '"'
);

header(
    'Cache-Control: max-age=0'
);


//==================================================
// GENERAR ARCHIVO
//==================================================

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;