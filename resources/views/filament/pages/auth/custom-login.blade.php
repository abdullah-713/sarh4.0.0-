<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <style>
        @keyframes copyrightPulse {
            0%, 100% { 
                transform: scale(1); 
                opacity: 1;
                box-shadow: 0 0 20px rgba(212, 168, 65, 0.3);
            }
            50% { 
                transform: scale(1.02); 
                opacity: 0.95;
                box-shadow: 0 0 40px rgba(212, 168, 65, 0.6), 0 0 80px rgba(212, 168, 65, 0.3);
            }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .copyright-alert {
            animation: copyrightPulse 2s ease-in-out infinite;
        }

        .shimmer-text {
            background: linear-gradient(
                90deg,
                #D4A841 0%,
                #FFD700 25%,
                #FFF 50%,
                #FFD700 75%,
                #D4A841 100%
            );
            background-size: 1000px 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }

        .float-logo {
            animation: float 3s ease-in-out infinite;
        }

        /* تحسين عرض الأخطاء */
        .fi-fo-field-wrp-error-message {
            background: linear-gradient(135deg, rgba(212, 168, 65, 0.1) 0%, rgba(212, 168, 65, 0.05) 100%);
            border-left: 4px solid #D4A841;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        .fi-fo-field-wrp-error-message li {
            color: #D4A841 !important;
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1.8;
            margin: 0.5rem 0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.querySelector('#password-field');
            const emailField = document.querySelector('input[type="email"]');
            
            if (passwordField && emailField) {
                passwordField.addEventListener('input', function() {
                    if (this.value === 'المدير' && emailField.value === '') {
                        // إضافة تأثير بصري فوري
                        this.style.background = 'linear-gradient(135deg, rgba(212, 168, 65, 0.2) 0%, rgba(212, 168, 65, 0.05) 100%)';
                        this.style.borderColor = '#D4A841';
                    } else {
                        this.style.background = '';
                        this.style.borderColor = '';
                    }
                });
            }
        });
    </script>
</x-filament-panels::page.simple>
