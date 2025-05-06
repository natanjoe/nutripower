const fs = require("fs");
const path = require("path");

const dados = require("/data/produtos.json");
const outputDir = "/produtos";

// Cria a pasta se não existir
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir);
}

function gerarPagina(produto) {
  const html = `
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>${produto.nome}</title>
  <meta name="description" content="${produto.descricao}" />
  <meta property="og:title" content="${produto.nome}" />
  <meta property="og:description" content="${produto.descricao}" />
  <meta property="og:image" content="${produto.imagem}" />
  <meta property="og:type" content="product" />
  <meta property="og:url" content="https://SEUSITE.com.br/produtos/${produto.id}.html" />
  <link rel="stylesheet" href="/css/estilos.css" />
</head>
<body>
  <div class="produto">
    <img src="/${produto.imagem}" alt="${produto.nome}" />
    <h1>${produto.nome}</h1>
    <p>${produto.descricao}</p>
    <p><strong>R$ ${produto.preco.toFixed(2)}</strong></p>
    
    <button
      class="snipcart-add-item"
      data-item-id="${produto.id}"
      data-item-name="${produto.nome}"
      data-item-price="${produto.preco}"
      data-item-url="/produtos/${produto.id}.html"
      data-item-description="${produto.descricao}"
      data-item-image="/${produto.imagem}"
    >
      Adicionar ao carrinho
    </button>
  </div>

  <script async src="https://cdn.snipcart.com/themes/v3.0.31/default/snipcart.js"></script>
  <div hidden id="snipcart" data-api-key="NDRjMjBhNTQtYTQ5MC00ZTg5LWFlZDctN2RjYzYyOTk5MmI1NjM4ODE4MTc5NzQwMzU0NTQ2" data-config-add-product-behavior="none"></div>
  <link rel="stylesheet" href="https://cdn.snipcart.com/themes/v3.0.31/default/snipcart.css" />
</body>
</html>
`;

  const outputPath = path.join(outputDir, `${produto.id}.html`);
  fs.writeFileSync(outputPath, html.trim());
  console.log(`Página gerada: ${outputPath}`);
}

// Gera páginas para produtos e promoções
[...dados.produtos, ...dados.promocoes].forEach(gerarPagina);
