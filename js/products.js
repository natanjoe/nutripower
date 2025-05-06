document.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('/data/produtos.json');
    
    if (!response.ok) {
      throw new Error('Falha ao carregar produtos');
    }    
    const data = await response.json();
    renderProducts(data.produtos);

  } catch (error) {
    console.error('Erro:', error);
    // Fallback - Pode carregar produtos estáticos se o JSON falhar
    const fallbackProducts = [
      {
        id: 'whey-fallback',
        nome: 'Whey Protein (Fallback)',
        preco: 99.90,
        imagem: 'images/whey.jpg'
      }
    ];
    renderProducts(fallbackProducts);
  }
});

// Função MODIFICADA - Agora usa classes do Snipcart
function renderProducts(products) {
  const container = document.getElementById('produtos-container');
  if (!container) return;

  container.innerHTML = products.map(product => `
    <div class="product-card">
      <img src="${product.imagem}" alt="${product.nome}">
      <h3>${product.nome}</h3>
      <p class="price">R$ ${product.preco.toFixed(2)}</p>
      
      <!-- Alterado para usar snipcart-add-item -->
      <button class="snipcart-add-item my-add-to-cart-btn" 
        data-item-id="${product.id}"
        data-item-price="${product.preco}"
        data-item-name="${product.nome}"
        data-item-image="${product.imagem}"
        data-item-url="/produtos/${product.id}">
        Adicionar ao Carrinho
      </button>
    </div>
  `).join('');
}

  