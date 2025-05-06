class ShoppingCart {

  /*
  constructor() {
    // Espera o Snipcart ficar pronto
    document.addEventListener('snipcart.ready', () => {
      console.log("Snipcart pronto!");
      this.syncWithSnipcart(); // Só executa quando a API estiver disponível
    });
  }
    */
  constructor() {
    // Apenas monitora o carrinho, não tenta gerenciá-lo
    document.addEventListener('snipcart.ready', () => {
      console.log('Snipcart pronto!');
      this.setupEventListeners();
    });
  }


  setupEventListeners() {
    // Apenas para feedback visual
    document.addEventListener('click', (e) => {
      if (e.target.closest('.snipcart-add-item')) {
        console.log('Produto adicionado ao carrinho');
      }
    });
  }
  
  async setupSnipcart() {
    try {

      // Verificação adicional de segurança
      if (typeof Parse === 'undefined' || !Parse.Cloud) {
        throw new Error('Parse SDK não está disponível');
      }

      // Busca a API Key de forma segura
      const { apiKey } = await Parse.Cloud.run('getSnipcartConfig');
      
           // Cria container do Snipcart
      this.snipcartEl = document.createElement('div');
      this.snipcartEl.hidden = true;
      this.snipcartEl.id = 'snipcart';
      this.snipcartEl.dataset.apiKey = config.apiKey;
      this.snipcartEl.dataset.configModalStyle = 'side';
      document.body.appendChild(this.snipcartEl);
      
      // Carrega recursos externos
      await this.loadSnipcartResources();

    } catch (error) {
      console.error('Erro ao configurar Snipcart:', error);
      this.showErrorToUser('Erro no sistema de pagamentos');
    }
  }


  async loadSnipcartResources() {
    const loadResource = (tag, attributes) => new Promise((resolve, reject) => {
      const element = document.createElement(tag);
      Object.assign(element, attributes);
      element.onload = resolve;
      element.onerror = reject;
      document.head.appendChild(element);
    });

    await Promise.all([
      loadResource('script', {
        src: 'https://cdn.snipcart.com/themes/v3.3.0/default/snipcart.js',
        async: true
      }),
      loadResource('link', {
        rel: 'stylesheet',
        href: 'https://cdn.snipcart.com/themes/v3.3.0/default/snipcart.css'
      })
    ]);
  }
  
  // Método MODIFICADO - Adiciona sincronização com Snipcart
  addItem(product) {
    const existingItem = this.cartItems.find(item => item.id === product.id);
    
    if (existingItem) {
      existingItem.quantity++;
    } else {
      this.cartItems.push({
        ...product,
        quantity: 1
      });
    }
    
    this.updateCartUI();
    this.syncWithSnipcart(); // ← Novo método de sincronização
  }
  
  
  // Método NOVO - Sincroniza com Snipcart
  syncWithSnipcart() {
    if (!window.Snipcart) return;
    
    document.addEventListener('snipcart.ready', () => {
      const items = Snipcart.api.cart.items;
      // Limpa o carrinho do Snipcart
      Snipcart.api.cart.items().forEach(item => {
        Snipcart.api.cart.items.remove(item.id);
      });
      
      // Adiciona todos os itens atuais
      this.cartItems.forEach(item => {
        Snipcart.api.cart.items.add({
          id: item.id,
          name: item.name,
          price: item.price,
          quantity: item.quantity,
          url: window.location.href
        });
      });
    });
  }

  // Método MODIFICADO - Remove item também do Snipcart
  removeItem(productId) {
    this.cartItems = this.cartItems.filter(item => item.id !== productId);
    
    if (window.Snipcart) {
      Snipcart.api.cart.items.remove(productId);
    }    
    this.updateCartUI();
  }


  // Atualiza quantidade
  updateQuantity(productId, newQuantity) {
    const item = this.cartItems.find(item => item.id === productId);
    if (item) {
      item.quantity = Math.max(1, newQuantity);
      this.updateCartUI();
    }
  }
  // Atualiza a interface
  updateCartUI() {
    this.updateCartCounter();
    this.renderCartItems();
    this.updateTotal();
  }

  // Atualiza contador
  updateCartCounter() {
    const totalItems = this.cartItems.reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll('.cart-items').forEach(el => {
      el.textContent = totalItems;
    });
  }

  // Renderiza itens no carrinho
  renderCartItems() {
    const cartContainer = document.querySelector('.cart-content');
    if (!cartContainer) return;

    cartContainer.innerHTML = this.cartItems.map(item => `
      <div class="cart-item" data-id="${item.id}">
        <img src="${item.image}" alt="${item.name}">
        <div class="item-details">
          <h4>${item.name}</h4>
          <div class="item-controls">
            <button class="quantity-btn minus">-</button>
            <span class="quantity">${item.quantity}</span>
            <button class="quantity-btn plus">+</button>
          </div>
        </div>
        <div class="item-price">
          R$ ${(item.price * item.quantity).toFixed(2)}
          <button class="remove-item">Remover</button>
        </div>
      </div>
    `).join('') || '<p class="empty-cart">Seu carrinho está vazio</p>';
  }

  // Calcula total
  updateTotal() {
    const total = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalEl = document.querySelector('.total');
    if (totalEl) {
      totalEl.textContent = `R$ ${total.toFixed(2)}`;
    }
  }

  // Feedback visual ao adicionar item
  showAddedFeedback(productName) {
    const feedbackEl = document.createElement('div');
    feedbackEl.className = 'cart-feedback';
    feedbackEl.innerHTML = `
      <span>✔ ${productName} adicionado ao carrinho</span>
    `;
    document.body.appendChild(feedbackEl);
    
    setTimeout(() => {
      feedbackEl.classList.add('show');
      setTimeout(() => feedbackEl.remove(), 4000);
    }, 10);
  }

  // Event listeners
  initEventListeners() {
    // Delegation para eventos dinâmicos
    document.addEventListener('click', (e) => {
      // Botão adicionar ao carrinho
      if (e.target.closest('.add-to-cart')) {
        const btn = e.target.closest('.add-to-cart');
        this.addItem({
          id: btn.dataset.id,
          name: btn.dataset.name,
          price: parseFloat(btn.dataset.price),
          image: btn.dataset.image
        });
         
      }
      
      // Botões dentro do carrinho
      const cartItem = e.target.closest('.cart-item');
      if (cartItem) {
        const id = cartItem.dataset.id;
        
        if (e.target.closest('.remove-item')) {
          this.removeItem(id);
        }
        
        if (e.target.closest('.minus')) {
          const item = this.cartItems.find(item => item.id === id);
          if (item) this.updateQuantity(id, item.quantity - 1);
        }
        
        if (e.target.closest('.plus')) {
          const item = this.cartItems.find(item => item.id === id);
          if (item) this.updateQuantity(id, item.quantity + 1);
        }
      }
    });
    
    // Abrir/fechar carrinho
    document.querySelector('.cart-btn')?.addEventListener('click', this.toggleCart);
    document.querySelector('.close-cart')?.addEventListener('click', this.toggleCart);
  }

  toggleCart = () => {
    document.querySelector('.cart-overlay').classList.toggle('show');
    document.body.classList.toggle('no-scroll');
  }
}


 // ✅ Correção (inicialização imediata + verificação)
document.addEventListener('DOMContentLoaded', () => {
  window.cart = new ShoppingCart();
});