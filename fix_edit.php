<?php
$f = 'C:\xampp\htdocs\SHC\resources\views\admin\capacitaciones\cursos\edit.blade.php';
$c = file_get_contents($f);

$old = '<div class="form-group">
                            <label for="codigo_acceso">Código de Acceso</label>';

$new = '<div class="form-group mt-3">
                              <label for="plantilla_certificado_id"><i class="fas fa-certificate text-warning"></i> Plantilla de Certificado</label>
                              <select class="form-control" id="plantilla_certificado_id" name="plantilla_certificado_id">
                                  <option value="">Por defecto (Plantilla básica)</option>
                                  @if(isset($plantillas))
                                      @foreach($plantillas as $plantilla)
                                          <option value="{{ $plantilla->id }}" {{ old(\'plantilla_certificado_id\', $curso->plantilla_certificado_id) == $plantilla->id ? \'selected\' : \'\' }}>{{ $plantilla->nombre }}</option>
                                      @endforeach
                                  @endif
                              </select>
                              <small class="form-text text-muted">Elige la plantilla que se usará para generar el certificado de este curso.</small>
                          </div>

                          <div class="form-group">
                            <label for="codigo_acceso">Código de Acceso</label>';

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Replaced";
