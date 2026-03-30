<?php
$file = 'resources/views/welcome.blade.php';
$content = file_get_contents($file);

$startStr = '<div class="overlay">';
$posStart = strpos($content, $startStr);

$navStr = '<ul class="nav nav-tabs"';
$posNav = strpos($content, $navStr, $posStart);

$formEndStr = '</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';

$posFormEnd = strpos($content, '</form>');
while ($posFormEnd !== false) {
    // we need the last form close 
    $lastFormEnd = $posFormEnd;
    $posFormEnd = strpos($content, '</form>', $posFormEnd + 1);
}

// Extract what is inside $navStr to the end of last </form>
// Actually we need the end of tab-content which is wait...
$searchStr = '</form>
                            </div>
                        </div>';
$posAfterForm = strpos($content, $searchStr);
$posEndOfDivs = strpos($content, '<!-- Bootstrap JS -->');

// Let's use preg_match carefully just for this exact region
$success = preg_match('/<ul class="nav nav-tabs".*?<\/form>\s*<\/div>\s*<\/div>/s', $content, $m);

if ($success) {
    $formsHtml = $m[0];
    
    $replacement = '<div class="overlay" style="display:flex; align-items:center; min-height: 100vh;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <!-- FORMULARIO y Pestañas van aquí, envueltos opcionalmente en su card-right de fondo blanco -->
                    <div class="card-right" style="background-color: rgba(255,255,255,0.95); border-radius: 10px; padding: 20px;">
                        ' . $formsHtml . '
                    </div>
                </div>
            </div>
        </div>
    </div>';
    
    $startHtml = substr($content, 0, $posStart);
    // Add replacement
    $newContent = $startHtml . $replacement . "\n    \n    " . substr($content, $posEndOfDivs);
    
    // remove JS
    $newContent = preg_replace('/<!-- Carrusel JavaScript -->.*?<\/script>/s', '<!-- JS -->', $newContent);

    file_put_contents($file, $newContent);
    echo "Replaced successfully!\n";
} else {
    echo "Forms html not found\n";
}
