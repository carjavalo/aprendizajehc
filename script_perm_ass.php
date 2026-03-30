<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$role = "Consultor Asstracud";
$perms = ["tracking.logins", "tracking.operations", "reportes.view", "chat.access"];
$added = 0;
foreach($perms as $p) {
    $pid = DB::table("permissions")->where("name", $p)->value("id");
    if ($pid) {
        DB::table("role_permissions")->updateOrInsert(["role_name" => $role, "permission_id" => $pid]);
        $added++;
    }
}
echo "Added $added permissions for $role\n";

