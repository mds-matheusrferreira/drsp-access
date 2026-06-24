<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BaseExterna\AnaliseProcessoController;
use App\Http\Controllers\BaseExterna\InserirProcessoController;
use App\Http\Controllers\BaseExterna\NotaTecnicaController;
use App\Http\Controllers\BaseExterna\ParecerTecnicoController;
use App\Http\Controllers\Coordenacao\Automacoes\CneasController;
use App\Http\Controllers\Coordenacao\Planilhas\ExternoController;
use App\Http\Controllers\Coordenacao\Planilhas\VisdataCebasController;
use App\Http\Controllers\PrincipalController;
use Illuminate\Support\Facades\Route;

Route::get('/_version', function () {
    $v = cache()->remember('_app_version', 30, function () {
        $latest = 0;
        foreach ([app_path(), resource_path('views')] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if ($f->isFile()) {
                    $latest = max($latest, $f->getMTime());
                }
            }
        }

        return substr(md5((string) $latest), 0, 8);
    });

    return response()->json(['v' => $v]);
})->middleware('throttle:30,1');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PrincipalController::class, 'index'])->name('dashboard');
    Route::get('/principal/updated-at', [PrincipalController::class, 'updatedAt'])->name('principal.updated-at');
    Route::get('/principal/search', [PrincipalController::class, 'search'])->name('principal.search');
    Route::get('/principal/state-totals', [PrincipalController::class, 'stateTotals'])->name('principal.state-totals');
    Route::get('/principal/states/{uf}', [PrincipalController::class, 'stateRecords'])->name('principal.states.show');
    Route::get('/principal/states/{uf}/download', [PrincipalController::class, 'downloadState'])->name('principal.states.download');
    Route::get('/principal/download', [PrincipalController::class, 'downloadAll'])->name('principal.download');

    Route::middleware('base-externa.permission')->group(function () {
        Route::get('/base-externa/inserir-processo', [InserirProcessoController::class, 'create'])->name('base-externa.processos.create');
        Route::post('/base-externa/inserir-processo', [InserirProcessoController::class, 'store'])->name('base-externa.processos.store');
    });

    Route::get('/base-externa/analise-processo', [AnaliseProcessoController::class, 'index'])->name('base-externa.analise-processo.index');
    Route::get('/base-externa/analise-processo/editar', [AnaliseProcessoController::class, 'edit'])->name('base-externa.analise-processo.edit');
    Route::put('/base-externa/analise-processo', [AnaliseProcessoController::class, 'update'])->name('base-externa.analise-processo.update');
    Route::get('/base-externa/analise-processo/parecer-tecnico', [ParecerTecnicoController::class, 'edit'])->name('base-externa.analise-processo.parecer.edit');
    Route::put('/base-externa/analise-processo/parecer-tecnico', [ParecerTecnicoController::class, 'update'])->name('base-externa.analise-processo.parecer.update');
    Route::get('/base-externa/analise-processo/parecer-tecnico/pdf', [ParecerTecnicoController::class, 'pdf'])->name('base-externa.analise-processo.parecer.pdf');
    Route::get('/base-externa/analise-processo/nota-tecnica', [NotaTecnicaController::class, 'edit'])->name('base-externa.analise-processo.nota-tecnica.edit');
    Route::put('/base-externa/analise-processo/nota-tecnica', [NotaTecnicaController::class, 'update'])->name('base-externa.analise-processo.nota-tecnica.update');
    Route::get('/base-externa/analise-processo/nota-tecnica/pdf', [NotaTecnicaController::class, 'pdf'])->name('base-externa.analise-processo.nota-tecnica.pdf');

    Route::middleware('coordenacao.permission')->group(function () {
        // Coordenação e Planilhas
        Route::view('/coordenacao', 'coordenacao.index')->name('coordenacao.index');

        Route::prefix('/coordenacao/automacoes')->name('coordenacao.automacoes.')->group(function () {
            Route::get('/cneas', [CneasController::class, 'index'])->name('cneas');
            Route::post('/cneas/gerar', [CneasController::class, 'generate'])->name('cneas.generate');
            Route::get('/cneas/ultimo', [CneasController::class, 'downloadLatest'])->name('cneas.latest');
        });

        Route::prefix('/coordenacao/planilhas')->name('coordenacao.planilhas.')->group(function () {
            Route::get('/visdata-cebas', [VisdataCebasController::class, 'index'])->name('visdata-cebas');
            Route::post('/visdata-cebas/import', [VisdataCebasController::class, 'import'])->name('visdata-cebas.import');
            Route::get('/visdata-cebas/modelo', [VisdataCebasController::class, 'modelo'])->name('visdata-cebas.modelo');
            Route::get('/visdata-cebas/backup', [VisdataCebasController::class, 'backup'])->name('visdata-cebas.backup');
            Route::view('/processos', 'coordenacao.planilhas.processos')->name('processos');
            Route::view('/cneas', 'coordenacao.planilhas.cneas')->name('cneas');
            Route::get('/externo', [ExternoController::class, 'index'])->name('externo');
            Route::post('/externo/import', [ExternoController::class, 'import'])->name('externo.import');
            Route::get('/externo/modelo', [ExternoController::class, 'modelo'])->name('externo.modelo');
            Route::get('/externo/backup', [ExternoController::class, 'backup'])->name('externo.backup');
        });
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
