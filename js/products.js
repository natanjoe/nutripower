document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("/data/produtos.json");

    if (!response.ok) {
      throw new Error("Falha ao carregar produtos");
    }
    const data = await response.json();
    renderProducts(data.produtos);
  } catch (error) {
    console.error("Erro:", error);
    console.warn(
      "Carregando produtos de fallback. Verifique o arquivo /data/produtos.json"
    );

    // Fallback - Pode carregar produtos estáticos se o JSON falhar
    const fallbackProducts = [
      {
        id: "whey-fallback",
        nome: "Whey Protein (Fallback)",
        preco: 99.9,
        imagem: "images/whey.jpg",
      },
    ];
    renderProducts(fallbackProducts);
  }
});

function renderProducts(products) {
  const container = document.getElementById("produtos-container");
  if (!container) {
    console.warn('Elemento "produtos-container" não encontrado.');
    return;
  }

  container.innerHTML = products
    .map((product) => {
      const pesoGramas = product.peso ? product.peso : 0;
      return `
  <div class="product-card">
    <a href="${product.url}">
      <img src="${product.imagem}" alt="${product.nome}">
      <h3>${product.nome}</h3>
    </a>
    <p class="price">R$ ${product.preco.toFixed(2)}</p>
    <button class="snipcart-add-item btn"
      data-item-id="${product.id}"
      data-item-price="${product.preco}"
      data-item-name="${product.nome}"
      data-item-description="${product.descricao || "Sem descrição"}"
      data-item-url="${window.location.origin}${product.url || "/produtos"}"
      data-item-image="${
        product.imagem.startsWith("http")
          ? product.imagem
          : `${window.location.origin}/${product.imagem}`
      }"
      data-item-weight="${pesoGramas}"
      data-item-length="${product.comprimento || 0}"
      data-item-width="${product.largura || 0}"
      data-item-height="${product.altura || 0}"
    >
      Adicionar ao Carrinho
    </button>
  </div>`;
    })
    .join("");
}

