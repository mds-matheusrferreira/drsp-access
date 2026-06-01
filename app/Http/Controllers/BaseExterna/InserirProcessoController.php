<?php

namespace App\Http\Controllers\BaseExterna;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseExterna\StoreInserirProcessoRequest;
use App\Services\BaseExterna\AccessFormRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InserirProcessoController extends Controller
{
    public function __construct(private readonly AccessFormRepository $accessForm)
    {
    }

    public function create(): View
    {
        return view('base-externa.inserir-processo', [
            'ufs' => [
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
                'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
            ],
        ]);
    }

    public function store(StoreInserirProcessoRequest $request): RedirectResponse
    {
        $this->accessForm->create($request->validated());

        return redirect()
            ->route('base-externa.processos.create')
            ->with('success', 'Processo inserido com sucesso.');
    }
}
