// ============================================================
//  CASABELA — ARQUIVO DE CONFIGURAÇÃO
//  Edite aqui todas as informações do site e dos imóveis
// ============================================================

const CASABELA_CONFIG = {

  // --- SITE ---
  site: {
    nome: "CasaBela",
    slogan: "Luxo para Férias",
    email: "info@casabela.es",
    telefone: "+34 600 000 000",
    whatsapp: "+34600000000",           // só números, sem espaços
    moeda: "€",
    idioma: "pt",
  },

  // --- IMOVEIS ---
  // Para adicionar um imóvel, copie um bloco { ... } e cole abaixo,
  // separado por vírgula.
  imoveis: [
    {
      id: 1,
      nome: "Villa CasaBela",
      subtitulo: "Moradia de luxo com piscina e jardim",
      localizacao: "Ayamonte, Huelva — Espanha",
      descricao: `
        Descubra o paraíso no coração de Ayamonte. A Villa CasaBela é uma moradia
        espaçosa e luminosa com acabamentos de qualidade, jardim privado, piscina
        e vistas deslumbrantes ao pôr do sol. Ideal para famílias e grupos que
        procuram uma experiência inesquecível na fronteira entre Espanha e Portugal.
      `,
      // ---- PREÇOS (€/noite) ----
      precos: {
        temporada_baixa:  120,   // Out–Mar
        temporada_media:  180,   // Abr–Jun, Set
        temporada_alta:   250,   // Jul–Ago
        semana_minima: 7,        // nº mínimo de noites em alta temporada
      },
      // ---- CARACTERÍSTICAS ----
      quartos: 3,
      casas_banho: 2,
      pessoas_max: 8,
      area_m2: 180,
      // ---- COMODIDADES (ícones Unicode incluídos) ----
      comodidades: [
        "🏊 Piscina privada",
        "🌿 Jardim privado",
        "❄️ Ar condicionado",
        "📶 Wi-Fi grátis",
        "🍳 Cozinha equipada",
        "🚗 Estacionamento",
        "📺 Smart TV",
        "🧺 Máquina de lavar",
        "🔥 Churrasqueira",
        "🏖️ A 10 min da praia",
      ],
      // ---- FOTOS ----
      // Coloque as fotos na pasta /Fotos/ com nomes numerados.
      // foto 1 = foto de capa (aparece no card e no topo da página)
      fotos: [
        { src: "Fotos/1.jpg",  legenda: "Vista exterior ao pôr do sol"  },
        { src: "Fotos/2.jpg",  legenda: "Piscina e jardim"              },
        { src: "Fotos/3.jpg",  legenda: "Sala de estar"                 },
        { src: "Fotos/4.jpg",  legenda: "Cozinha equipada"              },
        { src: "Fotos/5.jpg",  legenda: "Quarto principal"              },
        { src: "Fotos/6.jpg",  legenda: "Casa de banho"                 },
        { src: "Fotos/7.jpg",  legenda: "Terraço"                       },
        { src: "Fotos/8.jpg",  legenda: "Vista para Ayamonte"           },
      ],
      // ---- MAPA (coordenadas Google Maps) ----
      mapa: {
        lat: 37.2143,
        lng: -7.4058,
        zoom: 14,
      },
      ativo: true,
    },

    // ---- Adicione mais imóveis aqui ----
    // {
    //   id: 2,
    //   nome: "Apartamento Mar",
    //   ...
    // },
  ],

  // --- DATAS BLOQUEADAS (reservas já confirmadas) ---
  // Formato: "AAAA-MM-DD"
  // Estas datas são lidas pelo calendário e ficam indisponíveis.
  // O admin.php atualiza este ficheiro automaticamente quando confirma reservas.
  // Pode também editar manualmente.
  datas_bloqueadas: {
    1: [
      // "2025-07-01", "2025-07-02",
    ],
  },

};
