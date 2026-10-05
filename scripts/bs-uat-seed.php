<?php
[$app, $directory, $state] = require __DIR__.'/bs-verification-bootstrap.php';
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$accounts = [];
$template = User::where('is_active', 1)->whereHas('company', fn ($q) => $q->where('is_active', 1))->firstOrFail();
$role = DB::table('roles')->whereNotIn('code', ['ADMIN', 'FINANCE', 'DIR'])->value('id');
foreach (['finance', 'owner', 'member'] as $tag) {
    $user = $template->replicate();
    $password = bin2hex(random_bytes(16));
    $user->username = 'uat_'.$tag.'_'.bin2hex(random_bytes(3));
    $user->NIK = $user->username;
    $user->nama = 'UAT BS '.ucfirst($tag);
    $user->roles_id = $role;
    $user->atasan_user_id = $accounts['finance']['id'] ?? null;
    $user->password = Hash::make($password);
    $user->save();
    $permissions = ['akses.pengajuan-bs','akses.transaksi-operasional','akses.transaksi-perjalanan-dinas','akses.cash-flow'];
    if ($tag === 'finance') $permissions = array_merge($permissions, ['akses.approval-finance', 'akses.master-data']);
    foreach ($permissions as $permission) { \Spatie\Permission\Models\Permission::findOrCreate($permission, 'web'); $user->givePermissionTo($permission); }
    $accounts[$tag] = ['id' => $user->id, 'username' => $user->username, 'password' => $password];
}
\App\Models\Periode::where('periode', now()->format('Y-m'))->update(['status' => 'OPEN']);
$city = DB::table('wilayah')->whereNotNull('parent_id')->where('teritory', 'Jawa')->first();
$category = DB::table('cost_category')->where('nama', 'like', '%bbm%')->first() ?? DB::table('cost_category')->first();
$data = ['database' => DB::connection()->getDatabaseName(), 'accounts' => $accounts, 'category_id' => $category->id,
    'city' => ['id' => $city->id, 'province_id' => $city->parent_id, 'territory' => $city->teritory], 'today' => now()->toDateString(), 'period' => now()->format('Ym')];
file_put_contents($directory.'/browser-fixture.json', json_encode($data, JSON_PRETTY_PRINT));
echo 'Prepared three isolated browser test accounts; credentials stored privately.'.PHP_EOL;
