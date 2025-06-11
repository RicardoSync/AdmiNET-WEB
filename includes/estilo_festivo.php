<?php
function obtenerEstiloFestivo() {
  $hoy = date('m-d');
  $festivos = [
    '12-24' => 'navidad',
    '12-25' => 'navidad',
    '10-31' => 'halloween',
    '11-02' => 'diademuertos',
    '09-15' => 'mexico',
    '09-16' => 'mexico',
    '04-09' => 'pascua', // ajusta la fecha real si lo deseas
    '08-31' => 'cumplehades',
  ];

  return $festivos[$hoy] ?? null;
}
?>
