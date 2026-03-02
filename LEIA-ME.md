# CasaBela — Guia de Instalação e Edição
## Estrutura de ficheiros

```
/                        ← raiz do servidor
├── index.html           ← Página principal (homepage)
├── imovel.html          ← Página do imóvel (galeria + calendário + reserva)
├── style.css            ← Estilos visuais (cores, fontes, etc.)
├── config.js            ← ⭐ FICHEIRO PRINCIPAL DE EDIÇÃO (textos, preços, fotos)
├── .htaccess            ← Configuração Apache (IONOS Linux)
├── web.config           ← Configuração IIS (IONOS Windows)
├── Fotos/               ← Pasta com as fotos dos imóveis
│   ├── 1.jpg            ← Foto de capa / hero (aparece no card e no topo)
│   ├── 2.jpg
│   ├── 3.jpg
│   └── ...              ← Adicione fotos com números seguidos
├── api/
│   ├── reservas.php     ← API de reservas (PHP)
│   └── contacto.php     ← API de contacto (PHP)
├── admin/
│   ├── index.php        ← Painel de administração
│   └── exportar.php     ← Exportar reservas CSV
└── dados/
    └── reservas.json    ← Base de dados das reservas (gerada automaticamente)
```

---

## 1. Instalação no IONOS

1. Faça login no IONOS → File Manager (ou use FTP/SFTP)
2. Navegue até a pasta `public_html` (ou `htdocs`)
3. Copie **todos** os ficheiros e pastas acima para essa pasta
4. Certifique-se que a pasta `dados/` tem permissões de escrita (chmod 755 ou 777)

---

## 2. Configuração inicial (OBRIGATÓRIO)

### Edite o ficheiro `config.js`:
- `email`, `telefone`, `whatsapp` — os seus contactos
- `nome`, `subtitulo`, `localizacao`, `descricao` — texto do imóvel
- `precos` — preços por época
- `quartos`, `casas_banho`, `pessoas_max`, `area_m2` — características
- `comodidades` — lista de serviços/equipamentos
- `fotos` — lista de fotos (certifique-se que existem na pasta /Fotos/)
- `mapa.lat` / `mapa.lng` — coordenadas do Google Maps

### Edite `api/reservas.php` (linha 10-11):
```php
define('EMAIL_ADMIN', 'o_seu_email@dominio.com');
define('SENHA_ADMIN', 'a_sua_senha_aqui');
```

### Edite `admin/index.php` (linha 11-12):
```php
define('SENHA_ADMIN', 'a_sua_senha_aqui');  // igual à de cima
define('EMAIL_ADMIN', 'o_seu_email@dominio.com');
```

---

## 3. Fotos

- Coloque as fotos na pasta `/Fotos/`
- Use nomes com números: `1.jpg`, `2.jpg`, `3.jpg`, etc.
- A **foto 1** é sempre a capa (aparece no card da homepage e no topo da página do imóvel)
- Formatos suportados: `.jpg`, `.jpeg`, `.png`, `.webp`
- Tamanho recomendado: 1400×900 px (landscape)

---

## 4. Painel de Administração

Aceda em: `https://www.casabela.es/admin/`

- **Login**: use a senha definida em `SENHA_ADMIN`
- **Ver reservas**: lista completa com filtros por estado
- **Confirmar / Cancelar**: clique nos botões — o calendário atualiza automaticamente
- **Exportar CSV**: descarrega Excel com todas as reservas

---

## 5. Adicionar mais imóveis

No `config.js`, na secção `imoveis: [...]`, copie o bloco de um imóvel e cole a seguir:

```js
{
  id: 2,
  nome: "Apartamento Mar",
  subtitulo: "...",
  localizacao: "...",
  descricao: `...`,
  precos: {
    temporada_baixa: 80,
    temporada_media: 120,
    temporada_alta: 160,
    semana_minima: 5,
  },
  quartos: 2,
  casas_banho: 1,
  pessoas_max: 4,
  area_m2: 80,
  comodidades: ["🏊 Piscina", "..."],
  fotos: [
    { src: "Fotos/apto2-1.jpg", legenda: "Sala" },
    ...
  ],
  mapa: { lat: 37.21, lng: -7.40, zoom: 14 },
  ativo: true,
},
```

---

## 6. Bloquear datas manualmente (sem reserva)

No `config.js`, em `datas_bloqueadas`, adicione as datas:

```js
datas_bloqueadas: {
  1: [
    "2025-08-01", "2025-08-02", "2025-08-03",
  ],
  2: [
    "2025-07-15",
  ],
},
```

---

## Suporte

Para ajuda técnica contacte o seu programador ou abra uma questão em suporte.
