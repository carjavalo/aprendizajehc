try { \Illuminate\Support\Facades\Mail::raw('Prueba', function (\) { \->to('cvalderrama.tcc@gmail.com')->subject('Prueba'); }); echo 'OK'; } catch(\Exception \) { echo \->getMessage(); }
