<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

DB::table("role_permissions")->where("role_name", "Consultor Agesoc")->whereIn("permission_id", DB::table("permissions")->where("name", "publicidad.view")->pluck("id"))->delete();
echo "Done\n";

