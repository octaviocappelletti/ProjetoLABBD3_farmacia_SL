<?php include("header.php"); ?>

<div class="app">

  <!-- ============================ SIDEBAR ============================ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="selo selo-sm"><span class="selo-texto">Farmácia · Uso Seguro</span></div>
      <div class="sidebar-brand-text">
        <strong>Farmácia Hospitalar</strong>
        <span>Hospital Santa Lúcia Bauru</span>
      </div>
    </div>
    <nav class="nav" id="nav"></nav>
    <div class="sidebar-foot">
      Portal de uso interno.<br>Conteúdo de exemplo — substitua pelos dados oficiais da unidade.
    </div>
  </aside>
  <div class="sidebar-scrim" id="sidebarScrim"></div>

  <div class="main">
    <!-- ============================ TOPBAR (mobile) ============================ -->
    <div class="topbar">
      <button id="menuBtn" aria-label="Abrir menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <div class="selo"><span class="selo-texto">Farmácia</span></div>
      <span class="topbar-title">Farmácia Hospitalar</span>
    </div>

    <!-- ============================ TOPSTRIP ============================ -->
    <div class="topstrip">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input id="globalSearch" placeholder="Buscar POPs, comunicados, medicamentos…" autocomplete="off">
      </div>
      <div class="topstrip-meta">
        <span class="pill-status"><span>Sistemas operacionais</span></span>
      </div>
    </div>

    <!-- ============================ CONTEÚDO ============================ -->
    <div class="content" id="content"><!-- views injetadas via JS --></div>
  </div>
</div>

<!-- ============================ MODAL COMUNICADO ============================ -->
<div class="modal-scrim" id="modalScrim">
  <div class="modal-box">
    <div class="modal-head">
      <h2 id="modalTitle"></h2>
      <button class="modal-close" id="modalClose" aria-label="Fechar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<script>
/* =====================================================================
   ÍCONES — glifos simples e consistentes (24x24, stroke), sem dependência externa
   ===================================================================== */
const ICONS = {
  home: '<path d="M4 11.5L12 4l8 7.5"/><path d="M6 10v9h12v-9"/><path d="M10 19v-5h4v5"/>',
  megaphone: '<path d="M3 11v3a1 1 0 001 1h2l4 4V6L6 10H4a1 1 0 00-1 1z"/><path d="M10 7l8-3v17l-8-3"/><path d="M19 10.5a3 3 0 010 4"/>',
  book: '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M4 5.5A2.5 2.5 0 016.5 3H20v17H6.5A2.5 2.5 0 014 17.5v-12z"/>',
  cap: '<path d="M2 9l10-4 10 4-10 4-10-4z"/><path d="M6 11.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-4.5"/>',
  pill: '<path d="M5 13.5L13.5 5a4.5 4.5 0 016.36 6.36L11.36 19.86A4.5 4.5 0 015 13.5z"/><path d="M9 9l6 6"/>',
  shield: '<path d="M12 3l8 3v6c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V6l8-3z"/><path d="M9 12l2.2 2.2L15.5 9.5"/>',
  flow: '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="12" cy="18" r="2.2"/><path d="M7 7l3.5 9M17 7l-3.5 9"/>',
  mail: '<path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>',
  chev: '<path d="M6 9l6 6 6-6"/>',
  paperclip: '<path d="M8 12l6-6a3 3 0 014.2 4.2l-8 8a5 5 0 01-7-7l7-7"/>',
  alert: '<path d="M12 3l9 16H3l9-16z"/><path d="M12 10v4"/><circle cx="12" cy="17" r="0.6"/>',
  check: '<path d="M5 13l4 4L19 7"/>',
  bell: '<path d="M9 18a3 3 0 006 0"/><path d="M6 11a6 6 0 1112 0v3.5l1.5 2.5h-15L6 14.5V11z"/>',
  download: '<path d="M12 4v11"/><path d="M7 11l5 5 5-5"/><path d="M5 19h14"/>',
  film: '<path d="M4 5h16v14H4z"/><path d="M4 9h16M4 15h16M9 5v4M9 15v4"/>',
  pin: '<path d="M12 21s7-6.2 7-11.5A7 7 0 105 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.3"/>',
  phone: '<path d="M5 4h3l2 5-2.5 2a11 11 0 005.5 5.5l2-2.5 5 2v3a1 1 0 01-1 1A16 16 0 014 5a1 1 0 011-1z"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
  send: '<path d="M3 11l18-8-8 18-2.5-7L3 11z"/>'
};
function ic(name, cls=''){ return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${ICONS[name]||''}</svg>`; }

/* =====================================================================
   CONTEÚDO — substitua estes dados pelos materiais oficiais da farmácia.
   Tudo o que aparece no portal vem destes objetos; não é necessário
   editar HTML/CSS para atualizar comunicados, POPs, treinamentos etc.
   ===================================================================== */

const NAV_ITEMS = [
  { id:'home', label:'Início', icon:'home' },
  { id:'comunicados', label:'Comunicados', icon:'megaphone', badge:()=>COMUNICADOS.length },
  { id:'pops', label:'POPs e Protocolos', icon:'book', badge:()=>POPS.length },
  { id:'treinamentos', label:'Treinamentos', icon:'cap' },
  { id:'padronizacao', label:'Padronização', icon:'pill' },
  { id:'seguranca', label:'Segurança do Paciente', icon:'shield' },
  { id:'fluxos', label:'Fluxos e Processos', icon:'flow' },
  { id:'contato', label:'Contato', icon:'mail' },
];

const COMUNICADOS = [
  {
    id:'c1', cat:'padronizacao', catLabel:'Padronização',
    titulo:'Atualização da Lista de Medicamentos Padronizados — Junho/2026',
    data:'24/06/2026',
    resumo:'Inclusão de 3 itens e substituição de apresentação de 2 antimicrobianos na lista vigente, após avaliação da Comissão de Farmácia e Terapêutica (CFT).',
    corpo:'A Comissão de Farmácia e Terapêutica aprovou, em reunião ordinária, a atualização da lista de medicamentos padronizados para o mês de junho. As principais alterações envolvem a inclusão de novas apresentações para adequação às boas práticas de fracionamento e a substituição de itens com restrição de fornecimento pelo distribuidor.\n\nAs unidades assistenciais devem observar a nova lista a partir de 01/07/2026. Estoques remanescentes dos itens substituídos poderão ser utilizados até o esgotamento, mediante orientação da farmácia clínica.\n\nDúvidas podem ser encaminhadas pelo canal de solicitações da farmácia.',
    anexo:'Lista_Padronizacao_Jun2026.pdf'
  },
  {
    id:'c2', cat:'desabastecimento', catLabel:'Desabastecimento',
    titulo:'Desabastecimento temporário — Cloridrato de Vancomicina 500mg',
    data:'21/06/2026',
    resumo:'Fornecedor informou indisponibilidade pontual. Estoque de segurança garante cobertura prevista de 12 dias; alternativa terapêutica orientada para novas prescrições.',
    corpo:'Em razão de instabilidade na cadeia de fornecimento, a Vancomicina 500mg pó para solução injetável encontra-se com fornecimento irregular. A farmácia mantém estoque estratégico para atendimento das prescrições em curso.\n\nPara novas prescrições, recomenda-se contato prévio com a farmácia clínica para avaliação de alternativa terapêutica e ajuste posológico, conforme nota técnica anexa.\n\nA situação será reavaliada semanalmente e novas atualizações serão publicadas neste canal.',
    anexo:'Nota_Tecnica_Vancomicina.pdf'
  },
  {
    id:'c3', cat:'protocolo', catLabel:'Protocolo',
    titulo:'Novo fluxo de solicitação de medicamentos não padronizados',
    data:'18/06/2026',
    resumo:'A solicitação passa a ser feita exclusivamente pelo formulário eletrônico da farmácia, com prazo de resposta de até 4 horas em dias úteis.',
    corpo:'A partir desta data, solicitações de medicamentos não padronizados deverão ser registradas pelo canal eletrônico da farmácia (seção "Fluxos e Processos" deste portal), substituindo o formulário em papel.\n\nO novo fluxo prevê: registro da solicitação justificada pelo médico assistente, análise farmacêutica de pertinência clínica e disponibilidade, e retorno em até 4 horas em dias úteis (8 horas em finais de semana e feriados).\n\nCasos de urgência devem ser comunicados também por telefone ao ramal da farmácia central.',
    anexo:'Fluxo_Medicamento_Nao_Padronizado.pdf'
  },
  {
    id:'c4', cat:'alerta', catLabel:'Alerta sanitário',
    titulo:'Alerta ANVISA — recolhimento de lote de Insulina Regular',
    data:'15/06/2026',
    resumo:'Recolhimento preventivo de lote específico por desvio de qualidade. Farmácia já realizou conferência de estoque; nenhum frasco do lote identificado na unidade.',
    corpo:'Em atenção ao alerta da ANVISA, a farmácia realizou conferência imediata do estoque de Insulina Regular. Não foram localizados frascos pertencentes ao lote objeto do recolhimento nesta unidade.\n\nAinda assim, recomenda-se que a equipe assistencial confira o número de lote de frascos já distribuídos para os setores antes da administração, conforme rotina padrão de checagem dupla para medicamentos de alta vigilância.\n\nEm caso de identificação do lote, suspender o uso e notificar a farmácia imediatamente.',
    anexo:'Alerta_Anvisa_Insulina.pdf'
  },
  {
    id:'c5', cat:'orientacao', catLabel:'Orientação institucional',
    titulo:'Horário de funcionamento da Farmácia Central — feriado',
    data:'10/06/2026',
    resumo:'Atendimento em horário reduzido no feriado, com plantão para urgências mantido em regime 24h.',
    corpo:'No feriado do próximo dia 24, a Farmácia Central funcionará em horário reduzido (8h às 14h) para dispensação de rotina. O plantão farmacêutico para urgências e setores críticos permanece em regime de 24 horas, conforme escala afixada no mural da farmácia.\n\nSolicitações de medicamentos não padronizados serão analisadas no primeiro dia útil subsequente, exceto em caráter de urgência clínica.',
    anexo:null
  },
];

const POPS = [
  { codigo:'POP-FAR-001', versao:'v4.0', titulo:'Dispensação de medicamentos em unidades de internação', cat:'dispensacao', catLabel:'Dispensação', data:'12/05/2026' },
  { codigo:'POP-FAR-014', versao:'v2.1', titulo:'Armazenamento de medicamentos termolábeis (cadeia fria)', cat:'cadeia-fria', catLabel:'Cadeia fria', data:'03/04/2026' },
  { codigo:'POP-FAR-022', versao:'v3.2', titulo:'Manejo de medicamentos de alta vigilância', cat:'alta-vigilancia', catLabel:'Alta vigilância', data:'28/05/2026' },
  { codigo:'POP-FAR-030', versao:'v2.0', titulo:'Dispensação e controle de antimicrobianos', cat:'antimicrobianos', catLabel:'Antimicrobianos', data:'15/02/2026' },
  { codigo:'POP-FAR-045', versao:'v1.3', titulo:'Fracionamento de formas sólidas orais', cat:'fracionamento', catLabel:'Fracionamento', data:'20/01/2026' },
  { codigo:'POP-FAR-052', versao:'v2.4', titulo:'Manipulação de nutrição parenteral', cat:'farmacotecnica', catLabel:'Farmacotécnica hospitalar', data:'09/06/2026' },
  { codigo:'POP-FAR-007', versao:'v3.0', titulo:'Armazenamento geral de medicamentos e correlatos', cat:'armazenamento', catLabel:'Armazenamento', data:'02/03/2026' },
  { codigo:'POP-FAR-038', versao:'v1.1', titulo:'Reconstituição e diluição de antimicrobianos injetáveis', cat:'antimicrobianos', catLabel:'Antimicrobianos', data:'11/11/2025' },
];

const POP_CATEGORIAS = [
  { id:'todas', label:'Todas' },
  { id:'dispensacao', label:'Dispensação' },
  { id:'armazenamento', label:'Armazenamento' },
  { id:'cadeia-fria', label:'Cadeia fria' },
  { id:'alta-vigilancia', label:'Alta vigilância' },
  { id:'antimicrobianos', label:'Antimicrobianos' },
  { id:'fracionamento', label:'Fracionamento' },
  { id:'farmacotecnica', label:'Farmacotécnica' },
];

const TREINAMENTOS = [
  { titulo:'Uso seguro de medicamentos de alta vigilância', publico:'Enfermagem e Farmácia', duracao:'25 min', desc:'Identificação, dupla checagem e boas práticas no manuseio de medicamentos com risco aumentado de dano.' },
  { titulo:'Armazenamento correto de termolábeis e cadeia fria', publico:'Enfermagem, Farmácia e Almoxarifado', duracao:'18 min', desc:'Faixas de temperatura, rotina de monitoramento e conduta em caso de desvio térmico.' },
  { titulo:'Erros de medicação: identificação e prevenção', publico:'Toda equipe assistencial', duracao:'30 min', desc:'Os "5 certos" da administração de medicamentos e como notificar eventos adversos.' },
  { titulo:'Antimicrobianos e stewardship hospitalar', publico:'Médicos e Farmácia Clínica', duracao:'40 min', desc:'Princípios do uso racional de antimicrobianos e papel da farmácia no programa institucional.' },
];

const MEDICAMENTOS_PADRONIZADOS = [
  { nome:'Dipirona sódica', classe:'Analgésico/Antitérmico', apresentacao:'500mg/mL — solução injetável', status:'padronizado' },
  { nome:'Ceftriaxona sódica', classe:'Antimicrobiano', apresentacao:'1g — pó p/ solução injetável', status:'padronizado' },
  { nome:'Enoxaparina sódica', classe:'Anticoagulante', apresentacao:'40mg/0,4mL — seringa preenchida', status:'padronizado' },
  { nome:'Vancomicina (em desabastecimento)', classe:'Antimicrobiano', apresentacao:'500mg — pó p/ solução injetável', status:'alternativa' },
  { nome:'Omeprazol', classe:'Inibidor de bomba de prótons', apresentacao:'40mg — pó p/ solução injetável', status:'padronizado' },
  { nome:'Midazolam', classe:'Sedativo/hipnótico', apresentacao:'5mg/mL — solução injetável', status:'padronizado' },
];

const ATUALIZACOES_PADRONIZACAO = [
  { tipo:'Inclusão', item:'Sulfato de magnésio 10% — ampola 10mL', data:'24/06/2026' },
  { tipo:'Exclusão', item:'Apresentação anterior de Ceftriaxona 250mg (descontinuada pelo fabricante)', data:'24/06/2026' },
];

const NOTAS_TECNICAS = [
  { titulo:'Nota técnica — manejo de alternativa terapêutica à Vancomicina', data:'21/06/2026' },
  { titulo:'Nota técnica — uso de Sulfato de Magnésio em pré-eclâmpsia', data:'05/06/2026' },
];

const LASA_PARES = [
  { a:'Hidr<span class="hl">ALA</span>zina', b:'Hidr<span class="hl">OXI</span>zina', nota:'Anti-hipertensivo × anti-histamínico. Confirmar indicação clínica antes da dispensação.' },
  { a:'Clona<span class="hl">ZE</span>pam', b:'Clo<span class="hl">BA</span>zam', nota:'Ambos anticonvulsivantes/benzodiazepínicos — checar dose e apresentação.' },
  { a:'Insulina <span class="hl">NPH</span>', b:'Insulina <span class="hl">Regular</span>', nota:'Tempo de ação distinto. Sempre confirmar tipo antes da administração.' },
  { a:'Do<span class="hl">PA</span>mina', b:'Do<span class="hl">BU</span>tamina', nota:'Aminas vasoativas com mecanismos diferentes — risco elevado em UTI.' },
];

const ALTA_VIGILANCIA = ['Insulinas (todas as apresentações)','Heparina e demais anticoagulantes','Cloreto de Potássio concentrado','Opioides injetáveis','Quimioterápicos antineoplásicos','Bloqueadores neuromusculares','Soluções concentradas de eletrólitos','Sedativos para sedação profunda'];

const CHECAGEM_DUPLA = [
  { titulo:'Identificação do paciente', texto:'Confirmar nome completo e data de nascimento, conferindo com a pulseira de identificação.' },
  { titulo:'Confirmação do medicamento', texto:'Verificar nome, concentração, apresentação e validade do item a ser administrado.' },
  { titulo:'Checagem da prescrição', texto:'Confrontar dose, via e horário com a prescrição médica vigente.' },
  { titulo:'Assinatura dos dois profissionais', texto:'Ambos os profissionais envolvidos na checagem assinam o registro antes da administração.' },
];

const FLUXOS = [
  { titulo:'Solicitação de medicamento não padronizado', passos:[
    {t:'Abertura da solicitação', d:'Médico assistente registra a justificativa clínica no formulário eletrônico.'},
    {t:'Triagem farmacêutica', d:'Farmácia avalia pertinência clínica e disponibilidade de alternativa padronizada.'},
    {t:'Parecer e aquisição', d:'Em caso de aprovação, farmácia inicia processo de aquisição ou empréstimo entre unidades.'},
    {t:'Liberação ao setor', d:'Medicamento é liberado e o solicitante é notificado pelo prazo definido em protocolo.'},
  ]},
  { titulo:'Fluxo de devolução de medicamentos', passos:[
    {t:'Identificação do item', d:'Setor separa o medicamento não utilizado, íntegro e dentro da validade.'},
    {t:'Registro da devolução', d:'Preenchimento do formulário de devolução com motivo e responsável.'},
    {t:'Conferência farmacêutica', d:'Farmácia verifica condições de armazenamento e integridade da embalagem.'},
    {t:'Retorno ao estoque ou descarte', d:'Item retorna ao estoque ativo ou é destinado a descarte conforme normas vigentes.'},
  ]},
  { titulo:'Perdas e ajustes de estoque', passos:[
    {t:'Identificação da divergência', d:'Equipe identifica perda, quebra ou divergência no inventário.'},
    {t:'Registro formal', d:'Abertura de ocorrência com descrição, quantidade e setor envolvido.'},
    {t:'Análise da farmácia', d:'Avaliação da causa raiz e classificação (operacional, técnica ou administrativa).'},
    {t:'Ajuste no sistema', d:'Correção do saldo de estoque e arquivamento da documentação de suporte.'},
  ]},
  { titulo:'Fluxo de emergência', passos:[
    {t:'Acionamento', d:'Setor aciona a farmácia pelo ramal de emergência, informando o medicamento necessário.'},
    {t:'Priorização imediata', d:'Farmacêutico interrompe atividade de rotina para atendimento prioritário.'},
    {t:'Entrega expressa', d:'Medicamento é encaminhado ao setor por mensageiro dedicado ou retirada direta.'},
    {t:'Registro retroativo', d:'Documentação formal da dispensação é completada em até 2 horas.'},
  ]},
  { titulo:'Atendimento a setores críticos', passos:[
    {t:'Padronização específica', d:'UTI, centro cirúrgico e emergência possuem kits e padronização própria revisados semestralmente.'},
    {t:'Reposição programada', d:'Farmácia realiza checagem e reposição de kits em horários fixos, sem necessidade de solicitação.'},
    {t:'Canal direto', d:'Setores críticos possuem ramal direto com farmacêutico de plantão.'},
    {t:'Auditoria periódica', d:'Farmácia realiza auditoria mensal de validade e integridade dos kits.'},
  ]},
];

const KPIS = [
  { num:'98,2%', label:'Conformidade em auditorias de dispensação', trend:'+0,6 p.p. vs mês anterior', trendType:'up' },
  { num:'312', label:'POPs vigentes na biblioteca', trend:'4 revisados este mês', trendType:'flat' },
  { num:'47', label:'Treinamentos concluídos no mês', trend:'+12 vs mês anterior', trendType:'up' },
  { num:'1', label:'Evento adverso grave notificado no mês', trend:'Em análise pela CCIH', trendType:'warn' },
];

/* =====================================================================
   ROTEAMENTO E RENDERIZAÇÃO
   ===================================================================== */
let currentView = 'home';
let popFiltro = { cat:'todas', q:'' };
let comFiltro = { cat:'todas' };

function catTagClass(cat){
  return {protocolo:'tag-protocolo', padronizacao:'tag-padronizacao', desabastecimento:'tag-desabastecimento', alerta:'tag-alerta', orientacao:'tag-orientacao'}[cat] || 'tag-orientacao';
}

function renderNav(){
  document.getElementById('nav').innerHTML = NAV_ITEMS.map(item => `
    <button class="nav-item ${item.id===currentView?'active':''}" data-view="${item.id}">
      ${ic(item.icon)}
      <span>${item.label}</span>
      ${item.badge ? `<span class="badge">${item.badge()}</span>` : ''}
    </button>
  `).join('');
  document.querySelectorAll('.nav-item').forEach(btn=>{
    btn.addEventListener('click', ()=>{ navigate(btn.dataset.view); closeMobileMenu(); });
  });
}

function renderHome(){
  return `
    <div class="page-head">
      <div>
        <span class="eyebrow">Painel principal</span>
        <h1>Bem-vindo ao portal da Farmácia Hospitalar</h1>
        <p>Acesso rápido a comunicados, protocolos e indicadores da assistência farmacêutica do Hospital Santa Lúcia Bauru.</p>
      </div>
    </div>

    <div class="hero-stamp">
      <div class="selo"><span class="selo-texto">Farmácia Hospitalar · Uso Seguro de Medicamentos</span></div>
      <div class="hero-stamp-text">
        <span class="eyebrow">Compromisso institucional</span>
        <h1>Uso seguro e racional de medicamentos, em cada etapa da assistência.</h1>
        <p>Este portal reúne os protocolos, comunicados e materiais de educação continuada da Farmácia Hospitalar para toda a equipe do hospital.</p>
      </div>
    </div>

    <div class="grid grid-4 kpi-row">
      ${KPIS.map(k=>`
        <div class="card kpi">
          <div class="kpi-num">${k.num}</div>
          <div class="kpi-label">${k.label}</div>
          <span class="kpi-trend trend-${k.trendType}">${k.trend}</span>
        </div>
      `).join('')}
    </div>

    <div class="dash-grid">
      <div class="dash-col">
        <div class="card" style="padding:18px 18px 8px;">
          <div class="section-title">${ic('megaphone')} Comunicados recentes <span class="ver-tudo" data-go="comunicados">Ver todos</span></div>
          ${COMUNICADOS.slice(0,4).map(c=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('megaphone')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${c.titulo}</div>
                <div class="list-item-meta">
                  <span class="tag ${catTagClass(c.cat)}">${c.catLabel}</span>
                  <span class="mono">${c.data}</span>
                </div>
              </div>
            </div>
          `).join('')}
        </div>

        <div class="card" style="padding:18px 18px 8px; margin-top:20px;">
          <div class="section-title">${ic('book')} POPs mais acessados <span class="ver-tudo" data-go="pops">Ver biblioteca</span></div>
          ${POPS.slice(0,4).map(p=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('book')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${p.titulo}</div>
                <div class="list-item-meta"><span class="mono">${p.codigo} · ${p.versao}</span><span>${p.catLabel}</span></div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>

      <div class="dash-col">
        <div class="card" style="padding:18px;">
          <div class="section-title">${ic('alert')} Alertas de segurança medicamentosa</div>
          <div class="alert-row crit">
            ${ic('alert')}
            <div><div class="alert-row-title">Recolhimento de lote — Insulina Regular</div><div class="alert-row-text">Conferir número de lote antes da administração. Ver comunicado completo.</div></div>
          </div>
          <div class="alert-row">
            ${ic('alert')}
            <div><div class="alert-row-title">Desabastecimento — Vancomicina 500mg</div><div class="alert-row-text">Estoque de segurança garante cobertura de 12 dias.</div></div>
          </div>
        </div>

        <div class="card" style="padding:18px 18px 8px; margin-top:20px;">
          <div class="section-title">${ic('cap')} Treinamentos em destaque <span class="ver-tudo" data-go="treinamentos">Ver todos</span></div>
          ${TREINAMENTOS.slice(0,2).map(t=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('cap')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${t.titulo}</div>
                <div class="list-item-meta"><span class="mono">${t.duracao}</span><span>${t.publico}</span></div>
              </div>
            </div>
          `).join('')}
        </div>

        <div class="card" style="padding:18px 18px 8px; margin-top:20px;">
          <div class="section-title">${ic('pill')} Padronização — atualizações</div>
          ${ATUALIZACOES_PADRONIZACAO.map(a=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('pill')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${a.tipo}: ${a.item}</div>
                <div class="list-item-meta"><span class="mono">${a.data}</span></div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

function renderComunicados(){
  const cats = [{id:'todas',label:'Todas'},{id:'protocolo',label:'Protocolo'},{id:'padronizacao',label:'Padronização'},{id:'desabastecimento',label:'Desabastecimento'},{id:'alerta',label:'Alerta sanitário'},{id:'orientacao',label:'Orientação'}];
  const lista = COMUNICADOS.filter(c=> comFiltro.cat==='todas' || c.cat===comFiltro.cat);
  return `
    <div class="page-head">
      <div><span class="eyebrow">Comunicação oficial</span><h1>Comunicados da Farmácia</h1><p>Publicações oficiais sobre protocolos, padronização, desabastecimentos e alertas sanitários.</p></div>
    </div>
    <div class="filter-row">
      ${cats.map(c=>`<button class="chip ${comFiltro.cat===c.id?'active':''}" data-comcat="${c.id}">${c.label}</button>`).join('')}
    </div>
    <div id="comList">
      ${lista.length? lista.map(c=>`
        <div class="card com-card" data-comid="${c.id}">
          <div class="com-card-top">
            <span class="tag ${catTagClass(c.cat)}">${c.catLabel}</span>
            <span class="com-card-date mono">${c.data}</span>
          </div>
          <h3>${c.titulo}</h3>
          <p>${c.resumo}</p>
          <div class="com-card-foot">
            <button class="btn btn-ghost" data-comid="${c.id}">Ler comunicado completo →</button>
            ${c.anexo? `<span class="anexo-link">${ic('paperclip')} ${c.anexo}</span>` : ''}
          </div>
        </div>
      `).join('') : `<div class="empty">Nenhum comunicado nesta categoria.</div>`}
    </div>
  `;
}

function renderPops(){
  const lista = POPS.filter(p=>{
    const matchCat = popFiltro.cat==='todas' || p.cat===popFiltro.cat;
    const q = popFiltro.q.trim().toLowerCase();
    const matchQ = !q || p.titulo.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q);
    return matchCat && matchQ;
  });
  return `
    <div class="page-head">
      <div><span class="eyebrow">Documentação técnica</span><h1>Biblioteca de POPs e Protocolos</h1><p>Procedimentos Operacionais Padrão vigentes, organizados por categoria e com controle de versão.</p></div>
    </div>
    <div class="lib-toolbar">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input id="popSearch" placeholder="Buscar por título ou código…" value="${popFiltro.q}">
      </div>
    </div>
    <div class="filter-row">
      ${POP_CATEGORIAS.map(c=>`<button class="chip ${popFiltro.cat===c.id?'active':''}" data-popcat="${c.id}">${c.label}</button>`).join('')}
    </div>
    <div id="popList">
      ${lista.length? lista.map(p=>`
        <div class="card pop-card">
          <div class="pop-card-tab"></div>
          <div class="pop-card-body">
            <div class="pop-card-code mono">${p.codigo}<span class="ver">${p.versao}</span></div>
            <div class="pop-card-main">
              <h3>${p.titulo}</h3>
              <div class="pop-card-main-meta">
                <span class="tag tag-orientacao">${p.catLabel}</span>
                <span class="mono">Atualizado em ${p.data}</span>
              </div>
            </div>
            <div class="pop-card-actions">
              <button class="btn btn-primary" onclick="alert('Download de exemplo — conecte este botão ao arquivo real do POP (PDF).')">${ic('download')} PDF</button>
            </div>
          </div>
        </div>
      `).join('') : `<div class="empty">Nenhum POP encontrado para esta busca.</div>`}
    </div>
  `;
}

function renderTreinamentos(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Educação continuada</span><h1>Treinamentos da Farmácia Hospitalar</h1><p>Capacitações sobre uso seguro de medicamentos, com vídeo, material de apoio e avaliação.</p></div>
    </div>
    <div class="grid grid-2">
      ${TREINAMENTOS.map(t=>`
        <div class="card train-card">
          <div class="train-thumb">${ic('film')}<span class="dur mono">${t.duracao}</span></div>
          <div class="train-body">
            <h3>${t.titulo}</h3>
            <p>${t.desc}</p>
            <div class="train-audience">${ic('cap')} Público-alvo: ${t.publico}</div>
            <div class="train-actions">
              <button class="btn btn-primary" onclick="alert('Player de exemplo — incorpore aqui o vídeo do YouTube privado ou Google Drive.')">${ic('film')} Assistir</button>
              <button class="btn" onclick="alert('Material de apoio em PDF — anexe aqui o arquivo real.')">${ic('download')} Material</button>
              <button class="btn" onclick="alert('Questionário de avaliação — conecte aqui o Google Forms do treinamento.')">${ic('check')} Avaliação</button>
            </div>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

function renderPadronizacao(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Padronização institucional</span><h1>Padronização de Medicamentos</h1><p>Lista vigente, atualizações recentes e notas técnicas da Comissão de Farmácia e Terapêutica.</p></div>
    </div>
    <div class="card" style="margin-bottom:20px;">
      <div class="table-wrap">
        <table class="std-table">
          <thead><tr><th>Medicamento</th><th>Classe</th><th>Apresentação</th><th>Status</th></tr></thead>
          <tbody>
            ${MEDICAMENTOS_PADRONIZADOS.map(m=>`
              <tr>
                <td><strong>${m.nome}</strong></td>
                <td>${m.classe}</td>
                <td>${m.apresentacao}</td>
                <td><span class="status-dot status-${m.status}">${m.status==='padronizado'?'Padronizado':'Alternativa em uso'}</span></td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>
    <div class="grid grid-2">
      <div class="card" style="padding:18px;">
        <div class="section-title">${ic('pill')} Atualizações recentes</div>
        ${ATUALIZACOES_PADRONIZACAO.map(a=>`
          <div class="list-item"><div class="list-item-icon">${ic('pill')}</div>
            <div class="list-item-body"><div class="list-item-title">${a.tipo}</div><div class="list-item-meta">${a.item}<br><span class="mono">${a.data}</span></div></div>
          </div>
        `).join('')}
      </div>
      <div class="card" style="padding:18px;">
        <div class="section-title">${ic('book')} Notas técnicas farmacêuticas</div>
        ${NOTAS_TECNICAS.map(n=>`
          <div class="list-item"><div class="list-item-icon">${ic('book')}</div>
            <div class="list-item-body"><div class="list-item-title">${n.titulo}</div><div class="list-item-meta"><span class="mono">${n.data}</span></div></div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

function renderSeguranca(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Segurança do paciente</span><h1>Segurança do Paciente — Farmácia</h1><p>Conteúdo crítico sobre medicamentos LASA, alta vigilância e prevenção de erros de medicação.</p></div>
    </div>

    <div class="section-title">${ic('alert')} Medicamentos com nome ou aparência semelhante (LASA)</div>
    <div style="margin-bottom:26px;">
      ${LASA_PARES.map(l=>`
        <div class="card lasa-pair">
          <span class="lasa-word">${l.a}</span><span class="lasa-vs">vs</span><span class="lasa-word">${l.b}</span>
          <span class="lasa-note">${l.nota}</span>
        </div>
      `).join('')}
    </div>

    <div class="grid grid-2" style="margin-bottom:26px;">
      <div>
        <div class="section-title">${ic('shield')} Medicamentos de alta vigilância</div>
        <div class="vigilancia-grid">
          ${ALTA_VIGILANCIA.map(v=>`<div class="card vigilancia-item">${ic('alert')}<span>${v}</span></div>`).join('')}
        </div>
      </div>
      <div>
        <div class="section-title">${ic('check')} Protocolo de dupla checagem</div>
        <div class="card" style="padding: 6px 20px;">
          <div class="check-steps">
            ${CHECAGEM_DUPLA.map((c,i)=>`
              <div class="check-step">
                <div class="check-step-num">${i+1}</div>
                <div class="check-step-text"><strong>${c.titulo}</strong><span>${c.texto}</span></div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="padding:20px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
      <div style="flex:1; min-width:220px;">
        <div class="section-title" style="margin-bottom:6px;">${ic('bell')} Identificou um erro de medicação ou evento adverso?</div>
        <p style="font-size:12.5px; color:var(--gray);">A notificação é essencial para a melhoria contínua dos processos. Não é necessário identificar o profissional.</p>
      </div>
      <button class="btn btn-primary" data-go="contato">${ic('send')} Notificar evento</button>
    </div>
  `;
}

function renderFluxos(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Processos operacionais</span><h1>Fluxos e Processos da Farmácia</h1><p>Etapas padronizadas para solicitações, devoluções, ajustes de estoque e atendimentos especiais.</p></div>
    </div>
    <div id="flowList">
      ${FLUXOS.map((f,i)=>`
        <div class="card flow-acc ${i===0?'open':''}" data-flow="${i}">
          <button class="flow-acc-head" data-flowtoggle="${i}">
            <span class="num mono">0${i+1}</span>
            <h3>${f.titulo}</h3>
            ${ic('chev')}
          </button>
          <div class="flow-acc-body">
            <div class="flow-steps">
              ${f.passos.map((p,j)=>`
                <div class="flow-step">
                  <div class="flow-step-inner">
                    <div class="flow-step-num mono">${j+1}</div>
                    <div class="flow-step-title">${p.t}</div>
                    <div class="flow-step-text">${p.d}</div>
                  </div>
                </div>
                ${j<f.passos.length-1? `<div class="flow-arrow">${ic('chev')}</div>` : ''}
              `).join('')}
            </div>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

function renderContato(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Canal direto</span><h1>Contato e Solicitações</h1><p>Solicite medicamentos, tire dúvidas técnicas ou notifique problemas diretamente à Farmácia Hospitalar.</p></div>
    </div>
    <div class="contact-grid">
      <div class="card form-card">
        <div class="confirm-box" id="confirmBox">
          ${ic('check')}
          <div><strong>Solicitação registrada</strong><span>Este é um formulário de demonstração — conecte-o ao seu sistema real de chamados ou e-mail institucional.</span></div>
        </div>
        <form id="contactForm">
          <div class="field-row">
            <div class="field"><label>Nome completo</label><input required placeholder="Seu nome"></div>
            <div class="field"><label>Setor</label><input required placeholder="Ex.: UTI Adulto"></div>
          </div>
          <div class="field">
            <label>Tipo de solicitação</label>
            <select>
              <option>Solicitação de medicamento não padronizado</option>
              <option>Dúvida técnica</option>
              <option>Sugestão</option>
              <option>Notificação de problema / evento adverso</option>
            </select>
          </div>
          <div class="field"><label>Mensagem</label><textarea required placeholder="Descreva sua solicitação com o máximo de detalhes possível…"></textarea></div>
          <button class="btn btn-primary btn-block" type="submit">${ic('send')} Enviar solicitação</button>
        </form>
      </div>
      <div class="card" style="padding:22px 24px;">
        <div class="section-title" style="margin-bottom:16px;">Canais diretos</div>
        <div class="contact-info-item">${ic('phone')}<div><strong>Ramal da Farmácia Central</strong><span>Ramal 4521 — dias úteis, 7h às 19h</span></div></div>
        <div class="contact-info-item">${ic('mail')}<div><strong>E-mail institucional</strong><span>farmacia.hospitalar@hospitalsantalucia.exemplo</span></div></div>
        <div class="contact-info-item">${ic('clock')}<div><strong>Plantão farmacêutico</strong><span>24 horas, todos os dias, via ramal de urgência 4599</span></div></div>
        <div class="contact-info-item">${ic('pin')}<div><strong>Localização</strong><span>Farmácia Central — Bloco A, térreo</span></div></div>
      </div>
    </div>
  `;
}

const RENDERERS = {
  home: renderHome, comunicados: renderComunicados, pops: renderPops,
  treinamentos: renderTreinamentos, padronizacao: renderPadronizacao,
  seguranca: renderSeguranca, fluxos: renderFluxos, contato: renderContato,
};

function navigate(view){
  if(!RENDERERS[view]) return;
  currentView = view;
  document.getElementById('content').innerHTML = `<div class="view active">${RENDERERS[view]()}</div>`;
  renderNav();
  bindViewEvents(view);
  document.getElementById('content').scrollTop = 0;
  window.scrollTo(0,0);
}

function bindViewEvents(view){
  document.querySelectorAll('[data-go]').forEach(el=> el.addEventListener('click', ()=> navigate(el.dataset.go)));

  if(view==='comunicados'){
    document.querySelectorAll('[data-comcat]').forEach(el=> el.addEventListener('click', ()=>{ comFiltro.cat = el.dataset.comcat; navigate('comunicados'); }));
    document.querySelectorAll('[data-comid]').forEach(el=> el.addEventListener('click', ()=> openComunicado(el.dataset.comid)));
  }
  if(view==='pops'){
    document.querySelectorAll('[data-popcat]').forEach(el=> el.addEventListener('click', ()=>{ popFiltro.cat = el.dataset.popcat; navigate('pops'); }));
    const input = document.getElementById('popSearch');
    if(input){
      input.addEventListener('input', ()=>{
        const pos = input.selectionStart;
        popFiltro.q = input.value;
        navigate('pops');
        const newInput = document.getElementById('popSearch');
        newInput.focus();
        newInput.setSelectionRange(pos,pos);
      });
    }
  }
  if(view==='fluxos'){
    document.querySelectorAll('[data-flowtoggle]').forEach(el=>{
      el.addEventListener('click', ()=>{
        const card = el.closest('.flow-acc');
        card.classList.toggle('open');
      });
    });
  }
  if(view==='contato'){
    const form = document.getElementById('contactForm');
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      document.getElementById('confirmBox').classList.add('show');
      form.reset();
      document.getElementById('confirmBox').scrollIntoView({behavior:'smooth', block:'center'});
    });
  }
}

function openComunicado(id){
  const c = COMUNICADOS.find(x=>x.id===id);
  if(!c) return;
  document.getElementById('modalTitle').textContent = c.titulo;
  document.getElementById('modalBody').innerHTML = `
    <div class="com-card-top" style="margin-bottom:14px;">
      <span class="tag ${catTagClass(c.cat)}">${c.catLabel}</span>
      <span class="com-card-date mono">${c.data}</span>
    </div>
    <div class="com-detail-body">${c.corpo.split('\n\n').map(p=>`<p>${p}</p>`).join('')}</div>
    ${c.anexo? `<div style="margin-top:16px;"><span class="anexo-link">${ic('paperclip')} ${c.anexo}</span></div>` : ''}
  `;
  document.getElementById('modalScrim').classList.add('show');
}

/* =====================================================================
   INTERAÇÕES GLOBAIS — menu mobile, busca global, modal
   ===================================================================== */
function openMobileMenu(){ document.getElementById('sidebar').classList.add('open'); document.getElementById('sidebarScrim').classList.add('show'); }
function closeMobileMenu(){ document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarScrim').classList.remove('show'); }
document.getElementById('menuBtn').addEventListener('click', openMobileMenu);
document.getElementById('sidebarScrim').addEventListener('click', closeMobileMenu);

document.getElementById('modalClose').addEventListener('click', ()=> document.getElementById('modalScrim').classList.remove('show'));
document.getElementById('modalScrim').addEventListener('click', (e)=>{ if(e.target.id==='modalScrim') document.getElementById('modalScrim').classList.remove('show'); });
document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') document.getElementById('modalScrim').classList.remove('show'); });

document.getElementById('globalSearch').addEventListener('keydown', (e)=>{
  if(e.key==='Enter'){
    const q = e.target.value.trim();
    if(!q) return;
    popFiltro = { cat:'todas', q:q };
    navigate('pops');
  }
});

/* inicialização */
renderNav();
navigate('home');
</script>

<?php include("footer.php"); ?>
