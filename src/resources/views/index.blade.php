
<!-- Balise meta pour le token CSRF -->
@if(!app()->runningInConsole())
<meta name="csrf-token" content="{{ csrf_token() }}">
@endif

<!-- Bouton flottant pour ouvrir le menu dev -->
<div id="dev-toggle-btn" class="dev-toggle-button" onclick="toggleDevMenu()">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
    </svg>
    <span class="dev-tooltip">Menu Dev</span>
</div>

<!-- Menu de développement -->
<div id="dev-menu" class="dev-menu">
    <div class="dev-menu-header">
        <h3>Outils Développeur</h3>
        <button class="dev-close-btn" onclick="toggleDevMenu()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <div class="dev-menu-content">
        {{-- User switcher - EN HAUT --}}
        <div class="dev-menu-item">
            <div class="dev-item-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Changer d'utilisateur</span>
                <button onclick="toggleUserInfo()" class="dev-info-btn" title="Afficher les informations de l'utilisateur connecté">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m9,12 3,3 3,-3"></path>
                        <path d="M12 6v0"></path>
                    </svg>
                </button>
            </div>

            @php $users = App\Models\User::limit(20)->get(); @endphp

            <select onchange="changeUser(this, '{{env('APP_URL')}}')" class="dev-select">
                <option value="0" selected disabled>Sélectionner un utilisateur</option>
                @foreach ($users as $user)

                    @php $label = $user->name ?? $user->nom ?? $user->email ?? 'ID: ' . $user->id; @endphp
                    <option value="{{ $user->id }}">{{ $label }}</option>

                @endforeach
            </select>

            <div id="user-info" style="display: none; margin-top: 10px; background: #f8f9fa; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb;">
                @auth
                    @dump(auth()->user())
                @else
                    <span style="color: #6b7280;">Aucun utilisateur connecté</span>
                @endauth
            </div>
        </div>

        {{-- Vue et Controller côte à côte --}}
        <div class="dev-menu-item">
            <div class="dev-view-controller-grid">
                {{-- Vue --}}
                <div class="dev-view-controller-item">
                    @if (!isset($__CURRENT_VIEW__))
                        @php $__CURRENT_VIEW__ = request()->route()->getAction()['view'] ?? null; @endphp
                    @endif
                    
                    @php $viewLInk = "vscode://file/".$__CURRENT_VIEW__ ; @endphp
                    <div class="dev-item-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                        </svg>
                        <span>Vue actuelle</span>
                    </div>
                    @php $titleviewLink = str_replace('vscode://file/', '', $viewLInk); @endphp
                    <a href="{{ $viewLInk }}" class="dev-link" title="{{$titleviewLink}}" >Ouvrir dans VS Code</a>
                </div>

                {{-- Controller --}}
                <div class="dev-view-controller-item">
                    @php
                        if ( isset(request()->route()->getAction()['controller']) && Str::contains(request()->route()->getAction()['controller'], 'App\\Http\\Controllers\\')) {
                            $go = "vscode://file/".app_path(ltrim( (string) request()->route()->getAction()['controller'], 'App/Http/Controllers/'));
                            $go = str_replace('\\','/',$go);$controller_name = request()->route()->getAction()['controller'];
                            list($controller, $method) = explode('@', $controller_name);
                            $reflection = new ReflectionMethod($controller, $method);
                            $line_number = $reflection->getStartLine();
                            $filename = $reflection->getFileName();
                            $lien = explode('@', $go)[0].'.php:'.$line_number;
                        }
                    @endphp
                    
                    <div class="dev-item-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        <span>Controller actuel</span>
                    </div>
                    @if ( isset($lien) && $lien != null)
                        @php $titleControllerLink = str_replace('vscode://file/', '', $lien); @endphp
                        <a href="{{ $lien }}" class="dev-link" title="{{$titleControllerLink}}"   >Ouvrir dans VS Code</a>
                    @else
                        <span class="dev-link-disabled">Aucun controller détecté</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Route actuelle --}}
        <div class="dev-menu-item">
            <div class="dev-item-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9,22 9,12 15,12 15,22"></polyline>
                </svg>
                <span>Route actuelle</span>
            </div>
            <div class="dev-info-text">
                <strong>{{ request()->route()->getName() ?? 'Non nommée' }}</strong><br>
                <small>{{ request()->method() }} {{ request()->path() }}</small>
            </div>
        </div>

        {{-- Liens utiles --}}
        <div class="dev-menu-item">
            <div class="dev-item-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Fichiers utiles</span>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="vscode://file/{{ storage_path('logs/laravel.log') }}" class="dev-link-small" title="{{ storage_path('logs/laravel.log') }}">Logs</a>
                <a href="vscode://file/{{ base_path('.env') }}" class="dev-link-small" title="{{ base_path('.env') }}">.env</a>
                <a href="vscode://file/{{ base_path('routes/web.php') }}" class="dev-link-small" title="{{ base_path('routes/web.php') }}">Routes</a>
            </div>
        </div>

        {{-- Cache Management --}}
        <div class="dev-menu-item">
            <div class="dev-item-header" onclick="toggleSection('cache')" style="cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="7.5,4.21 12,6.81 16.5,4.21"></polyline>
                    <polyline points="7.5,19.79 7.5,14.6 3,12"></polyline>
                    <polyline points="21,12 16.5,14.6 16.5,19.79"></polyline>
                </svg>
                <span>Gestion du Cache</span>
                <svg class="dev-chevron" id="cache-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </div>
            <div class="dev-section-content" id="cache-section" style="display: none; margin-top: 10px;">
                <div class="dev-command-grid">
                    <button onclick="executeCommand('cache:clear')" class="dev-cmd-btn">
                        <span>Vider le cache</span>
                        <small>cache:clear</small>
                    </button>
                    <button onclick="executeCommand('config:clear')" class="dev-cmd-btn">
                        <span>Config cache</span>
                        <small>config:clear</small>
                    </button>
                    <button onclick="executeCommand('route:clear')" class="dev-cmd-btn">
                        <span>Routes cache</span>
                        <small>route:clear</small>
                    </button>
                    <button onclick="executeCommand('view:clear')" class="dev-cmd-btn">
                        <span>Vues cache</span>
                        <small>view:clear</small>
                    </button>
                    <button onclick="executeCommand('optimize:clear')" class="dev-cmd-btn dev-cmd-danger">
                        <span>Tous les caches</span>
                        <small>optimize:clear</small>
                    </button>
                </div>
            </div>
        </div>

        {{-- Database Management --}}
        <div class="dev-menu-item">
            <div class="dev-item-header" onclick="toggleSection('database')" style="cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                <span>Base de données</span>
                <svg class="dev-chevron" id="database-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </div>
            <div class="dev-section-content" id="database-section" style="display: none; margin-top: 10px;">
                <div class="dev-command-grid">
                    <button onclick="executeCommand('migrate')" class="dev-cmd-btn">
                        <span>Migrations</span>
                        <small>migrate</small>
                    </button>
                    <button onclick="executeCommand('db:seed')" class="dev-cmd-btn">
                        <span>Seeders</span>
                        <small>db:seed</small>
                    </button>
                    <button onclick="executeCommand('migrate:fresh --seed')" class="dev-cmd-btn dev-cmd-danger">
                        <span>Reset + Seed</span>
                        <small>migrate:fresh --seed</small>
                    </button>
                    <button onclick="executeCommand('migrate:rollback')" class="dev-cmd-btn dev-cmd-warning">
                        <span>Rollback</span>
                        <small>migrate:rollback</small>
                    </button>
                    <button onclick="executeCommand('migrate:status')" class="dev-cmd-btn dev-cmd-info">
                        <span>Statut</span>
                        <small>migrate:status</small>
                    </button>
                </div>
            </div>
        </div>

        {{-- view Resquest->all() --}}
        @php $requestData = request()->all(); @endphp
        @if(!empty($requestData) && $requestData != '[]' && $requestData != null)
            <div class="dev-menu-item">
                <div class="dev-item-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Requête actuelle</span>
                </div>
                
                <button onclick="toggleRequestData()" class="dev-link" style="display: block; text-align: center; cursor: pointer; border: none;">
                    Afficher request()->all()
                </button>
                
                <div id="request-data" style="display: none; margin-top: 10px; background: #f8f9fa; padding: 10px; border-radius: 6px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">
                    @dump($requestData)
                </div>
            </div>

            <script>
                function toggleRequestData() {
                    const requestDataDiv = document.getElementById('request-data');
                    const button = event.target;
                    
                    if (requestDataDiv.style.display === 'none') {
                        requestDataDiv.style.display = 'block';
                        button.textContent = 'Masquer request()->all()';
                    } else {
                        requestDataDiv.style.display = 'none';
                        button.textContent = 'Afficher request()->all()';
                    }
                }
            </script>
        @endif
</div>

    <style>
        /* Bouton flottant en bas à droite */
        .dev-toggle-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            color: white;
            border: none;
        }

        .dev-toggle-button:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }

        .dev-tooltip {
            position: absolute;
            right: 65px;
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .dev-toggle-button:hover .dev-tooltip {
            opacity: 1;
        }

        /* Menu de développement */
        .dev-menu {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 480px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            transform: translateY(20px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            max-height: 85vh;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .dev-menu.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .dev-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #374151;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .dev-menu-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .dev-close-btn {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dev-close-btn:hover {
            background-color: #f1f5f9;
            color: #374151;
        }

        .dev-menu-content {
            padding: 18px;
            max-height: calc(85vh - 80px);
            overflow-y: auto;
        }

        .dev-menu-item {
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        .dev-menu-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .dev-item-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .dev-item-header svg {
            color: #6366f1;
        }

        .dev-info-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
        }

        .dev-info-btn:hover {
            color: #6366f1;
            background-color: #f0f4ff;
        }

        .dev-info-text {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            color: #374151;
        }

        .dev-info-text strong {
            color: #1e293b;
            font-weight: 600;
        }

        .dev-info-text small {
            color: #64748b;
        }

        .dev-link {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.2s ease;
            font-weight: 500;
            border: none;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
        }

        .dev-link:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            text-decoration: none;
            color: white;
        }

        .dev-link-small {
            display: inline-block;
            background: #64748b;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .dev-link-small:hover {
            background: #475569;
            text-decoration: none;
            color: white;
            transform: translateY(-1px);
        }

        .dev-link-disabled {
            display: inline-block;
            background: #f1f5f9;
            color: #94a3b8;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #e2e8f0;
        }

        .dev-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: all 0.2s ease;
            color: #374151;
        }

        .dev-select:focus {
            outline: none;
            border-color: #6366f1!important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

         .dev-select option {color: #000!important; /* Forcer la couleur du texte en noir */}

        /* Style pour Vue et Controller côte à côte */
        .dev-view-controller-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .dev-view-controller-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }

        .dev-view-controller-item .dev-item-header {
            margin-bottom: 8px;
            font-size: 13px;
        }

        .dev-view-controller-item .dev-link,
        .dev-view-controller-item .dev-link-disabled {
            font-size: 11px;
            padding: 8px 12px;
            display: block;
            text-align: center;
        }

        /* Nouvelles fonctionnalités - Sections pliables */
        .dev-section-content {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .dev-chevron {
            margin-left: auto;
            transition: transform 0.3s ease;
            color: #9ca3af;
        }

        .dev-chevron.rotated {
            transform: rotate(180deg);
        }

        .dev-command-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }

        .dev-cmd-btn {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
            text-align: center;
            color: #374151;
            position: relative;
            overflow: hidden;
        }

        .dev-cmd-btn:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dev-cmd-btn span {
            display: block;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .dev-cmd-btn small {
            color: #6b7280;
            font-size: 10px;
            font-family: 'Courier New', monospace;
        }

        .dev-cmd-btn.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .dev-cmd-btn.loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .dev-cmd-btn.success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #86efac;
            color: #166534;
        }

        .dev-cmd-btn.error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #fca5a5;
            color: #dc2626;
        }

        /* Variantes des boutons */
        .dev-cmd-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #fca5a5;
            color: #dc2626;
        }

        .dev-cmd-danger:hover {
            background: linear-gradient(135deg, #fecaca 0%, #f87171 100%);
            border-color: #ef4444;
            color: white;
        }

        .dev-cmd-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fcd34d;
            color: #92400e;
        }

        .dev-cmd-warning:hover {
            background: linear-gradient(135deg, #fde68a 0%, #f59e0b 100%);
            border-color: #d97706;
            color: white;
        }

        .dev-cmd-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #93c5fd;
            color: #1e40af;
        }

        .dev-cmd-info:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #3b82f6 100%);
            border-color: #2563eb;
            color: white;
        }

        /* Modal pour les résultats */
        .dev-result-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            z-index: 10000;
            max-width: 600px;
            max-height: 70vh;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .dev-result-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dev-result-content {
            padding: 20px 24px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #1f2937;
            color: #f9fafb;
            white-space: pre-wrap;
        }

        .dev-result-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dev-menu {
                width: calc(100vw - 40px);
                right: 20px;
            }
            
            .dev-view-controller-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .dev-command-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            }
        }

        /* Scrollbar personnalisée pour le menu */
        .dev-menu-content::-webkit-scrollbar {
            width: 6px;
        }

        .dev-menu-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .dev-menu-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dev-menu-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>




    <script>
        function changeUser(select, url) {
            let id = select.value;
            if (url.endsWith('/')) { 
                url = url.slice(0, -1); 
            }
           window.location.href = url + "/onlydev/change-user/" + id;
        }

        function toggleDevMenu() {
            const menu = document.getElementById('dev-menu');
            menu.classList.toggle('active');
        }

        function toggleUserInfo() {
            const userInfoDiv = document.getElementById('user-info');
            const button = event.target.closest('.dev-info-btn');
            const svg = button.querySelector('svg path:last-child');
            
            if (userInfoDiv.style.display === 'none') {
                userInfoDiv.style.display = 'block';
                svg.setAttribute('d', 'm9,15 3,-3 3,3');
            } else {
                userInfoDiv.style.display = 'none';
                svg.setAttribute('d', 'm9,12 3,3 3,-3');
            }
        }

        // Fermer le menu si on clique à l'extérieur
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('dev-menu');
            const toggleBtn = document.getElementById('dev-toggle-btn');
            
            if (!menu.contains(event.target) && !toggleBtn.contains(event.target)) {
                menu.classList.remove('active');
            }
        });

        // Nouvelles fonctions pour les commandes Artisan
        function toggleSection(sectionName) {
            const section = document.getElementById(sectionName + '-section');
            const chevron = document.getElementById(sectionName + '-chevron');
            
            if (section.style.display === 'none') {
                section.style.display = 'block';
                chevron.classList.add('rotated');
            } else {
                section.style.display = 'none';
                chevron.classList.remove('rotated');
            }
        }

        async function executeCommand(command) {
            const button = event.target.closest('.dev-cmd-btn');
            
            // Ajouter l'état de chargement
            button.classList.add('loading');
            button.disabled = true;
            
            try {
                // Obtenir le token CSRF
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    throw new Error('Token CSRF non trouvé');
                }

                const response = await fetch('/onlydev/execute-command', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ command: command })
                });

                const result = await response.json();

                if (result.success) {
                    showCommandResult(command, result);
                    button.classList.add('success');
                    setTimeout(() => button.classList.remove('success'), 2000);
                } else {
                    showCommandError(command, result.error);
                    button.classList.add('error');
                    setTimeout(() => button.classList.remove('error'), 3000);
                }

            } catch (error) {
                console.error('Erreur:', error);
                showCommandError(command, error.message);
                button.classList.add('error');
                setTimeout(() => button.classList.remove('error'), 3000);
            } finally {
                // Retirer l'état de chargement
                button.classList.remove('loading');
                button.disabled = false;
            }
        }

        function showCommandResult(command, result) {
            const modal = createResultModal(command, result.output, 'success');
            document.body.appendChild(modal);
            
            // Auto-fermeture pour les commandes de cache
            if (command.includes('clear') || command.includes('cache')) {
                setTimeout(() => {
                    if (modal.parentElement) {
                        modal.parentElement.removeChild(modal);
                    }
                }, 3000);
            }
        }

        function showCommandError(command, error) {
            const modal = createResultModal(command, error, 'error');
            document.body.appendChild(modal);
        }

        function createResultModal(command, content, type) {
            // Créer l'overlay
            const overlay = document.createElement('div');
            overlay.className = 'dev-result-overlay';
            
            // Créer la modal
            const modal = document.createElement('div');
            modal.className = 'dev-result-modal';
            
            const header = document.createElement('div');
            header.className = 'dev-result-header';
            
            const title = document.createElement('h3');
            title.style.margin = '0';
            title.style.fontSize = '16px';
            title.style.fontWeight = '600';
            title.textContent = `Résultat: ${command}`;
            
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = 'background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;';
            closeBtn.onclick = () => overlay.remove();
            
            header.appendChild(title);
            header.appendChild(closeBtn);
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'dev-result-content';
            contentDiv.textContent = content || 'Commande exécutée avec succès';
            
            modal.appendChild(header);
            modal.appendChild(contentDiv);
            overlay.appendChild(modal);
            
            // Fermer en cliquant sur l'overlay
            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    overlay.remove();
                }
            };
            
            return overlay;
        }
    </script>
