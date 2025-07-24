<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends ShieldLoginController
{
    protected $helpers = ['form']; // Carrega o helper de formulários

    /**
     * Sobrescreve o método de login para adicionar suporte a LDAP
     */
    public function loginAction(): RedirectResponse
    {
        // Carrega o serviço de validação
        $validation = \Config\Services::validation();

        $authType = $this->request->getPost('auth_type') ?? 'local';

        // Resetar validação ANTES de definir as regras
        $validation->reset();

        // Regras de validação diferentes para cada tipo
        $rules = [
            'password' => 'required',
        ];

        if ($authType === 'ldap') {
            $rules['cpf'] = 'required|regex_match[/^\d{11}$/]';
        } else {
            $rules['email'] = 'required|valid_email';
        }

        // Validar apenas os campos necessários
        if (!$validation->setRules($rules)->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $credentials = [
            'password' => $this->request->getPost('password'),
            'remember' => (bool) $this->request->getPost('remember')
        ];

        if ($authType === 'ldap') {
            // Para LDAP, usamos o username (CPF) como credencial
            $credentials['username'] = $this->request->getPost('cpf');
            $result = auth('ldap')->attempt($credentials);

            if ($result->isOK()) {
                auth('session')->login($result->extraInfo());
                return redirect()->to(config('Auth')->loginRedirect());
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $result->reason() ?? 'Credenciais LDAP inválidas');
        }

        // Autenticação local (email/senha)
        $credentials['email'] = $this->request->getPost('email');
        $result = auth('session')->attempt($credentials);

        if (!$result->isOK()) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result->reason());
        }

        return redirect()->to(config('Auth')->loginRedirect());
    }
}
