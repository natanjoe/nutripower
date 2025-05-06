
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

function renderProducts(products) {  
  const container = document.getElementById('produtos-container');
  if (!container) {
    console.warn('Elemento "produtos-container" não encontrado.');
    return;
  }
   
  container.innerHTML = products.map(product => `
    <div class="product-card">
      <img src="${product.imagem}" alt="${product.nome}">
      <h3>${product.nome}</h3>
      <p class="price">R$ ${product.preco.toFixed(2)}</p>
      <button class="snipcart-add-item my-add-to-cart-btn"
        data-item-id="${product.id}"
        data-item-price="${product.preco}"
        data-item-name="${product.nome}"
        data-item-description="${product.descricao || 'Sem descrição'}"
        data-item-url="${product.url}" 
        data-item-image="${product.imagem.startsWith('http') ? product.imagem : 'https://b9d2-2804-xxxx.ngrok-free.app/' + product.imagem}"
        style="cursor:pointer">
        Adicionar ao Carrinho
      </button>
    </div>
  `).join('');
}


//data-item-url="/produtos/${product.id}"