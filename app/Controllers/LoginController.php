<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends ShieldLoginController
{
    protected $helpers = ['form'];

    /**
     * Sobrescreve o método de login para adicionar suporte a LDAP
     */
    public function loginAction(): RedirectResponse
    {
        // Se já estiver logado, redireciona
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        $validation = \Config\Services::validation();
        $authType = $this->request->getPost('auth_type') ?? 'local';

        // Reset das regras de validação
        $validation->reset();

        // Define regras conforme tipo de autenticação
        $rules = [
            'password' => 'required|min_length[8]',
        ];

        if ($authType === 'ldap') {
            $rules['cpf'] = 'required|regex_match[/^\d{11}$/]';
        } else {
            $rules['email'] = 'required|valid_email';
        }

        // Valida os dados
        if (!$validation->setRules($rules)->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        try {
            if ($authType === 'ldap') {
                $credentials = [
                    'username' => $this->request->getPost('cpf'),
                    'password' => $this->request->getPost('password')
                ];

                $result = auth('ldap')->attempt($credentials);

                if ($result->isOK()) {
                    return redirect()->to(config('Auth')->loginRedirect())
                        ->with('message', 'Login realizado com sucesso!');
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $result->reason() ?? 'Credenciais inválidas');
            } else {
                // Autenticação local
                $credentials = [
                    'email' => $this->request->getPost('email'),
                    'password' => $this->request->getPost('password')
                ];

                $remember = (bool) $this->request->getPost('remember');
                $result = auth('session')->attempt($credentials, $remember);

                if ($result->isOK()) {
                    return redirect()->to(config('Auth')->loginRedirect())
                        ->with('message', 'Bem-vindo de volta!');
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $result->reason() ?? 'Credenciais inválidas');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro login: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro inesperado. Tente novamente.');
        }
    }
}
