document.addEventListener('DOMContentLoaded', () => {
    // Configurações
    let currentLanguage = 'pt';
    const jsonPath = '/textos/textos.json';
    
    // Inicializações
    initMobileMenu();
    initSmoothScroll();
    initScrollAnimations();
    initLanguageSelector();
    loadTexts();

    // Elementos
    const languageButtons = {
        pt: document.querySelector('[data-lang="pt"]'),
        en: document.querySelector('[data-lang="en"]')
    };

    // Função principal para carregar textos
    async function loadTexts() {
        try {
            const response = await fetch(jsonPath);
            if (!response.ok) throw new Error('Erro ao carregar JSON');
            
            const data = await response.json();
            if (!data[currentLanguage]) throw new Error('Idioma não encontrado');
            
            updateAllTexts(data[currentLanguage]);
        } catch (error) {
            console.error('Falha ao carregar textos:', error);
            showErrorToUser(error);
        }
    }

    // Atualiza todos os textos da página
    function updateAllTexts(texts) {
        // Atualiza elementos com data-text
        document.querySelectorAll('[data-text]').forEach(element => {
            try {
                const keys = element.getAttribute('data-text').split('.');
                let value = keys.reduce((obj, key) => obj?.[key], texts);
                
                if (value !== undefined) {
                    element.textContent = value;
                    updateLinkHref(element, keys, value);
                }
            } catch (error) {
                console.warn('Erro ao atualizar elemento:', error);
            }
        });
    }

    // Atualiza links de contato
    function updateLinkHref(element, keys, value) {
        if (element.tagName === 'A' && keys.includes('contato')) {
            const type = keys[keys.length - 1];
            const linkMap = {
                instagram: `https://instagram.com/${value.replace('@', '')}`,
                facebook: `https://facebook.com/${value.replace('@', '')}`,
                email: `mailto:${value}`,
                whatsapp: `https://wa.me/55${value.replace(/\D/g, '')}`
            };
            if (linkMap[type]){
                element.href = linkMap[type];

                 // Adiciona target="_blank" para redes sociais
                if (type === 'instagram' || type === 'facebook' || type === 'whatsapp') {
                    element.setAttribute('target', '_blank');
                    element.setAttribute('rel', 'noopener noreferrer');
                }
            } 
        }
    }

    // Menu mobile
    function initMobileMenu() {
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const menu = document.querySelector('.menu');
        menuBtn?.addEventListener('click', () => menu?.classList.toggle('active'));
    }

    function initSmoothScroll() {
        // Modifique o seletor para pegar APENAS links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                // Verifica se o link é para uma seção interna
                const targetId = this.getAttribute('href');
                if (targetId.startsWith('#')) {
                    e.preventDefault();
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 70,
                            behavior: 'smooth'
                        });
                        
                        // Fechar menu mobile se estiver aberto
                        document.querySelector('.menu')?.classList.remove('active');
                    }
                }
                // Links externos (como redes sociais) serão tratados normalmente
            });
        });
    }

    function initScrollAnimations() {
        const sections = document.querySelectorAll('section');
        
        // Configura estado inicial das animações
        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
    
        // Função para verificar visibilidade
        const checkVisibility = () => {
            sections.forEach(section => {
                const sectionTop = section.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (sectionTop < windowHeight - 100) {
                    section.classList.add('visible');
                }
            });
        };
    
        // Event listeners
        window.addEventListener('load', () => {
            // Delay para garantir que o conteúdo está carregado
            setTimeout(checkVisibility, 50);
        });
        
        window.addEventListener('scroll', checkVisibility);
        
        // Verificação inicial
        checkVisibility();
    }
    
    // Seletor de idioma
    function initLanguageSelector() {
        const selector = document.createElement('div');
        selector.className = 'language-selector';
        /*selector.innerHTML = `
        <button class="lang-btn active" data-lang="pt">🇧🇷 PT</button>
        <button class="lang-btn" data-lang="en">🇬🇧 EN</button>
        🇵🇹 (Portugal)
        🇺🇸 (EUA)
        `;*/
        document.body.prepend(selector);
        
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentLanguage = btn.getAttribute('data-lang');
                loadTexts();
            });
        });
    }

    // Mostra erros para o usuário
    function showErrorToUser(error) {
        const alert = document.createElement('div');
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            background: #ff4444;
            color: white;
            z-index: 1000;
            border-radius: 5px;
        `;
        alert.textContent = `Erro: ${error.message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }


});