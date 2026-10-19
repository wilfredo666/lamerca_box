<?php
require_once __DIR__ . "/fpdf.php";

/**
 * Etiquetas de 50 x 25 mm (horizontal) para impresora térmica.
 * Una página por encomienda, con código de barras Code 128.
 */
class EtiquetaEncomienda extends FPDF
{
  const ANCHO = 50;
  const ALTO = 25;
  const MARGEN = 2;

  const PATRONES = [
    "212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312",
    "132212", "221213", "221312", "231212", "112232", "122132", "122231", "113222",
    "123122", "123221", "223211", "221132", "221231", "213212", "223112", "312131",
    "311222", "321122", "321221", "312212", "322112", "322211", "212123", "212321",
    "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313",
    "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121",
    "313121", "211331", "231131", "213113", "213311", "213131", "311123", "311321",
    "331121", "312113", "312311", "332111", "314111", "221411", "431111", "111224",
    "111422", "121124", "121421", "141122", "141221", "112214", "112412", "122114",
    "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111",
    "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112",
    "421211", "212141", "214121", "412121", "111143", "111341", "131141", "114113",
    "114311", "411113", "411311", "113141", "114131", "311141", "411131", "211412",
    "211214", "211232", "2331112"
  ];

  public function __construct()
  {
    parent::__construct("L", "mm", [self::ALTO, self::ANCHO]);
    $this->SetMargins(self::MARGEN, self::MARGEN, self::MARGEN);
    $this->SetAutoPageBreak(false);
  }

  public function agregarEtiqueta($codigo, $destinatario, $detalle, $pie)
  {
    $this->AddPage("L", [self::ALTO, self::ANCHO]);
    $anchoUtil = self::ANCHO - 2 * self::MARGEN;

    $this->codigoBarras((string) $codigo, self::MARGEN, self::MARGEN, $anchoUtil, 9);

    $this->SetFont("Helvetica", "B", 11);
    $this->SetXY(self::MARGEN, 11.5);
    $this->Cell($anchoUtil, 4.5, $this->texto($codigo), 0, 0, "C");

    $this->SetFont("Helvetica", "B", 8);
    $this->SetXY(self::MARGEN, 17.2);
    $contacto = trim((string) $detalle);
    $textoContacto = $contacto !== "" ? "  " . $contacto : "";
    $this->SetFont("Helvetica", "", 8);
    $anchoContacto = min($this->GetStringWidth($this->texto($textoContacto)), $anchoUtil * 0.45);
    $this->SetFont("Helvetica", "B", 8);
    $nombre = $this->recortar($destinatario, $anchoUtil - $anchoContacto);
    $anchoNombre = $this->GetStringWidth($nombre);
    $this->SetFont("Helvetica", "", 8);
    $textoContacto = $this->recortar($textoContacto, $anchoUtil - $anchoNombre);
    $anchoTotal = $anchoNombre + $this->GetStringWidth($textoContacto);

    $x = self::MARGEN + ($anchoUtil - $anchoTotal) / 2;
    $this->SetXY($x, 16.8);
    $this->SetFont("Helvetica", "B", 8);
    $this->Cell($anchoNombre, 3.4, $nombre, 0, 0, "L");
    $this->SetFont("Helvetica", "", 8);
    $this->Cell($this->GetStringWidth($textoContacto), 3.4, $textoContacto, 0, 0, "L");

    $this->SetFont("Helvetica", "", 6.5);
    $this->SetXY(self::MARGEN, 20.8);
    $this->Cell($anchoUtil, 2.6, $this->recortar($pie, $anchoUtil), 0, 0, "C");
  }

  private function texto($valor)
  {
    $convertido = @iconv("UTF-8", "windows-1252//TRANSLIT//IGNORE", (string) $valor);
    return $convertido === false ? "" : $convertido;
  }

  private function recortar($valor, $ancho)
  {
    $texto = $this->texto($valor);
    while ($texto !== "" && $this->GetStringWidth($texto) > $ancho) {
      $texto = substr($texto, 0, -1);
    }
    return $texto;
  }

  private function codigoBarras($datos, $x, $y, $anchoMaximo, $alto)
  {
    $valores = [104];
    $longitud = strlen($datos);
    for ($i = 0; $i < $longitud; $i++) {
      $ascii = ord($datos[$i]);
      $valores[] = $ascii >= 32 && $ascii <= 126 ? $ascii - 32 : 0;
    }
    $suma = $valores[0];
    for ($i = 1; $i < count($valores); $i++) {
      $suma += $valores[$i] * $i;
    }
    $valores[] = $suma % 103;
    $valores[] = 106;

    $secuencia = "";
    foreach ($valores as $valor) {
      $secuencia .= self::PATRONES[$valor];
    }
    $modulos = array_sum(str_split($secuencia));
    $ancho = min(0.25, $anchoMaximo / $modulos);
    $total = $modulos * $ancho;
    $posicion = $x + ($anchoMaximo - $total) / 2;

    $this->SetFillColor(0, 0, 0);
    foreach (str_split($secuencia) as $indice => $grosor) {
      $largo = (int) $grosor * $ancho;
      if ($indice % 2 === 0) {
        $this->Rect($posicion, $y, $largo, $alto, "F");
      }
      $posicion += $largo;
    }
  }
}
