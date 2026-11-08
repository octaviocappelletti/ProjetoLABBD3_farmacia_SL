<?php
/**
 * Template Name: Portal Farmácia
 */
defined('ABSPATH') || exit;

$is_admin_user = current_user_can('edit_posts');

/* ---- Roteamento por slug ---- */
$slug_to_view = [
    'comunicados'  => 'comunicados',
    'pops'         => 'pops',
    'treinamentos' => 'treinamentos',
    'padronizacao' => 'padronizacao',
    'seguranca'    => 'seguranca',
    'fluxos'       => 'fluxos',
    'contato'      => 'contato',
];
$current_slug = get_post_field('post_name', get_the_ID());
$initial_view = $slug_to_view[$current_slug] ?? 'home';

$page_urls = [
    'home'         => trailingslashit(home_url('/')),
    'comunicados'  => trailingslashit(home_url('/comunicados')),
    'pops'         => trailingslashit(home_url('/pops')),
    'treinamentos' => trailingslashit(home_url('/treinamentos')),
    'padronizacao' => trailingslashit(home_url('/padronizacao')),
    'seguranca'    => trailingslashit(home_url('/seguranca')),
    'fluxos'       => trailingslashit(home_url('/fluxos')),
    'contato'      => trailingslashit(home_url('/contato')),
];
$json_page_urls = json_encode($page_urls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

/* ---- Queries ---- */
function fsl_get_posts(string $type, int $limit = 100): array {
    return get_posts(['post_type' => $type, 'numberposts' => $limit, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
}

$comunicados   = fsl_get_posts('comunicado');
$pops          = fsl_get_posts('pop_protocolo');
$treinamentos  = fsl_get_posts('treinamento');
$medicamentos  = fsl_get_posts('med_padronizado');
$lasa_pares    = fsl_get_posts('lasa_par');
$alta_vig      = fsl_get_posts('alta_vigilancia');
$fluxos        = fsl_get_posts('fluxo_processo');

function fsl_to_json($posts, string $type): string {
    $out = [];
    foreach ($posts as $p) {
        $m = get_post_meta($p->ID);
        $item = ['id' => $p->ID, 'titulo' => $p->post_title];
        switch ($type) {
            case 'comunicado':
                $item['tipo']    = $m['fsl_tipo'][0]    ?? '';
                $item['resumo']  = $m['fsl_resumo'][0]  ?? wp_trim_words($p->post_content, 25);
                $item['corpo']   = $p->post_content;
                $item['anexo']   = $m['fsl_anexo_url'][0] ?? '';
                $item['data']    = get_the_date('d/m/Y', $p);
                break;
            case 'pop_protocolo':
                $item['codigo']    = $m['fsl_codigo'][0]      ?? '';
                $item['versao']    = $m['fsl_versao'][0]      ?? '';
                $item['categoria'] = $m['fsl_categoria'][0]   ?? '';
                $item['arquivo']   = $m['fsl_arquivo_url'][0] ?? '';
                $item['data']      = get_the_date('d/m/Y', $p);
                break;
            case 'treinamento':
                $item['video']    = $m['fsl_video_url'][0] ?? '';
                $item['pdf']      = $m['fsl_pdf_url'][0]   ?? '';
                $item['quiz']     = $m['fsl_quiz_url'][0]  ?? '';
                $item['duracao']  = $m['fsl_duracao'][0]   ?? '';
                $item['publico']  = $m['fsl_publico'][0]   ?? '';
                $item['descricao']= $m['fsl_descricao'][0] ?? strip_tags($p->post_content);
                break;
            case 'med_padronizado':
                $item['classe']       = $m['fsl_classe'][0]       ?? '';
                $item['apresentacao'] = $m['fsl_apresentacao'][0] ?? '';
                $item['status']       = $m['fsl_status_med'][0]   ?? 'padronizado';
                break;
            case 'lasa_par':
                $item['med_a']  = $m['fsl_med_a'][0]    ?? '';
                $item['med_b']  = $m['fsl_med_b'][0]    ?? '';
                $item['nota']   = $m['fsl_nota_lasa'][0] ?? '';
                break;
            case 'alta_vigilancia':
                break; // só o título
            case 'fluxo_processo':
                $raw = $m['fsl_passos'][0] ?? '[]';
                $item['passos'] = json_decode($raw, true) ?: [];
                break;
        }
        $out[] = $item;
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}

$json_comunicados  = fsl_to_json($comunicados,  'comunicado');
$json_pops         = fsl_to_json($pops,         'pop_protocolo');
$json_treinamentos = fsl_to_json($treinamentos, 'treinamento');
$json_medicamentos = fsl_to_json($medicamentos, 'med_padronizado');
$json_lasa         = fsl_to_json($lasa_pares,   'lasa_par');
$json_alta         = fsl_to_json($alta_vig,     'alta_vigilancia');
$json_fluxos       = fsl_to_json($fluxos,       'fluxo_processo');

$nonce    = wp_create_nonce('wp_rest');
$rest_url = esc_url(get_rest_url(null, 'fsl/v1'));
$wp_rest  = esc_url(get_rest_url(null, 'wp/v2/'));
$admin_url = esc_url(admin_url());
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Portal — Farmácia Hospitalar · Hospital Hospital de Rede Privada</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Serif:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --red:#A80F14; --red-deep:#7A0B0F; --red-tint:#FBEAEA;
  --gray:#666; --white:#fff; --gray-light:#F5F5F5;
  --paper:#FBFAF8; --ink:#262321; --line:#E2DDD7; --line-soft:#EEEAE5;
  --amber:#92600A; --amber-bg:#FBF1DF; --green:#2E6B40; --green-bg:#E9F2EA;
  --f-display:"IBM Plex Serif",Georgia,serif;
  --f-body:"IBM Plex Sans",-apple-system,sans-serif;
  --f-mono:"IBM Plex Mono","SFMono-Regular",monospace;
  --radius:3px;
  --shadow-card:0 1px 2px rgba(38,35,33,.06),0 1px 1px rgba(38,35,33,.04);
  --shadow-pop:0 8px 24px rgba(38,35,33,.12);
  --sidebar-w:248px;
}
*{box-sizing:border-box}html{scroll-behavior:smooth}
body{margin:0;font-family:var(--f-body);color:var(--ink);background:var(--gray-light);-webkit-font-smoothing:antialiased}
h1,h2,h3,h4{font-family:var(--f-display);margin:0;line-height:1.2;font-weight:600}
p{margin:0}a{color:var(--red);text-decoration:none}button{font-family:var(--f-body);cursor:pointer}
:focus-visible{outline:2px solid var(--red);outline-offset:2px}.mono{font-family:var(--f-mono)}
@media(prefers-reduced-motion:reduce){*{animation-duration:.001ms!important;transition-duration:.001ms!important}}

/* SELO */
.selo{--size:92px;width:var(--size);height:var(--size);border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;position:relative;transform:rotate(-9deg);flex-shrink:0;color:var(--red)}
.selo::before{content:"";position:absolute;inset:6px;border:1px dashed currentColor;border-radius:50%;opacity:.55}
.selo-texto{font-family:var(--f-mono);font-size:8.5px;font-weight:600;letter-spacing:.5px;text-align:center;text-transform:uppercase;line-height:1.25;padding:0 10px}
.selo.selo-sm{--size:44px}.selo.selo-sm .selo-texto{font-size:5.6px;padding:0 5px}

/* LAYOUT */
.app{display:flex;min-height:100vh}
.sidebar{width:var(--sidebar-w);flex-shrink:0;background:var(--ink);color:var(--white);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:40;transition:transform .25s ease}
.sidebar-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:12px}
.sidebar-brand .selo{color:var(--white)}
.sidebar-brand-text{font-family:var(--f-display);line-height:1.25}
.sidebar-brand-text strong{display:block;font-size:14.5px;font-weight:600}
.sidebar-brand-text span{display:block;font-size:11px;color:rgba(255,255,255,.6);font-family:var(--f-body);margin-top:2px}
.nav{flex:1;padding:10px 12px;display:flex;flex-direction:column;gap:2px;overflow-y:auto}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:var(--radius);color:rgba(255,255,255,.72);font-size:13.5px;font-weight:500;background:none;border:none;text-align:left;width:100%;transition:background .15s,color .15s}
.nav-item svg{width:17px;height:17px;flex-shrink:0;opacity:.9}
.nav-item:hover{background:rgba(255,255,255,.07);color:var(--white)}
.nav-item.active{background:var(--red);color:var(--white)}
.nav-item .badge{margin-left:auto;font-family:var(--f-mono);font-size:10px;background:rgba(255,255,255,.14);padding:1px 6px;border-radius:10px}
.nav-item.active .badge{background:rgba(255,255,255,.25)}
.sidebar-foot{padding:14px 20px 18px;border-top:1px solid rgba(255,255,255,.12);font-size:11px;color:rgba(255,255,255,.5);line-height:1.6}

/* TOPBAR mobile */
.topbar{display:none;position:sticky;top:0;z-index:30;background:var(--ink);color:var(--white);align-items:center;gap:12px;padding:13px 16px}
.topbar button{background:none;border:none;color:var(--white);width:32px;height:32px;display:flex;align-items:center;justify-content:center}
.topbar-title{font-family:var(--f-display);font-size:14px;font-weight:600}
.sidebar-scrim{display:none}

/* MAIN */
.main{margin-left:var(--sidebar-w);flex:1;min-width:0;display:flex;flex-direction:column}
.topstrip{background:var(--white);border-bottom:1px solid var(--line);padding:16px 36px;display:flex;align-items:center;gap:18px;position:sticky;top:0;z-index:20}
.search{flex:1;max-width:480px;display:flex;align-items:center;gap:9px;background:var(--gray-light);border:1px solid var(--line);border-radius:var(--radius);padding:9px 13px}
.search svg{width:15px;height:15px;color:var(--gray);flex-shrink:0}
.search input{border:none;background:none;outline:none;width:100%;font-size:13.5px;font-family:var(--f-body);color:var(--ink)}
.topstrip-meta{margin-left:auto;display:flex;align-items:center;gap:16px;font-size:12.5px;color:var(--gray)}
.pill-status{display:flex;align-items:center;gap:6px;font-family:var(--f-mono);font-size:11.5px;color:var(--green);background:var(--green-bg);padding:5px 10px;border-radius:20px}
.pill-status::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.content{flex:1;padding:32px 36px 64px;max-width:1180px}
.view{display:none;animation:fadein .35s ease}.view.active{display:block}
@keyframes fadein{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
.page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:28px;padding-bottom:18px;border-bottom:1px solid var(--line)}
.page-head .eyebrow{font-family:var(--f-mono);font-size:11px;letter-spacing:1px;color:var(--red);text-transform:uppercase;margin-bottom:6px;display:block}
.page-head h1{font-size:26px}.page-head p{color:var(--gray);font-size:13.5px;margin-top:6px;max-width:560px}

/* COMPONENTES */
.card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow-card)}
.grid{display:grid;gap:18px}.grid-2{grid-template-columns:repeat(2,1fr)}.grid-3{grid-template-columns:repeat(3,1fr)}.grid-4{grid-template-columns:repeat(4,1fr)}
.section-title{display:flex;align-items:center;gap:9px;font-size:14px;font-weight:600;font-family:var(--f-display);margin-bottom:14px}
.section-title svg{width:16px;height:16px;color:var(--red)}.section-title .ver-tudo{margin-left:auto;font-family:var(--f-body);font-size:12px;font-weight:500;color:var(--red)}
.tag{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;padding:3px 8px;border-radius:20px;font-family:var(--f-body)}
.tag-protocolo{background:var(--red-tint);color:var(--red-deep)}.tag-padronizacao{background:var(--green-bg);color:var(--green)}.tag-desabastecimento{background:var(--amber-bg);color:var(--amber)}.tag-alerta{background:#2A2A2A;color:#fff}.tag-orientacao{background:var(--gray-light);color:var(--gray);border:1px solid var(--line)}
.btn{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;padding:8px 14px;border-radius:var(--radius);border:1px solid var(--line);background:var(--white);color:var(--ink);transition:border-color .15s,background .15s;cursor:pointer}
.btn svg{width:13px;height:13px}.btn:hover{border-color:var(--gray)}
.btn-primary{background:var(--red);border-color:var(--red);color:var(--white)}.btn-primary:hover{background:var(--red-deep);border-color:var(--red-deep)}
.btn-success{background:var(--green);border-color:var(--green);color:var(--white)}
.btn-block{width:100%;justify-content:center}.btn-ghost{border-color:transparent;padding-left:4px;padding-right:4px}
.btn-sm{padding:5px 10px;font-size:11.5px}
.empty{text-align:center;padding:38px 20px;color:var(--gray);font-size:13px}

/* DASHBOARD */
.hero-stamp{display:flex;align-items:center;gap:22px;background:var(--ink);color:var(--white);border-radius:var(--radius);padding:26px 30px;margin-bottom:26px;position:relative;overflow:hidden}
.hero-stamp::after{content:"";position:absolute;right:-40px;top:-60px;width:220px;height:220px;border:1px dashed rgba(255,255,255,.08);border-radius:50%}
.hero-stamp .selo{color:var(--white);--size:78px}
.hero-stamp-text .eyebrow{font-family:var(--f-mono);font-size:11px;letter-spacing:1px;color:#E7A6A8;text-transform:uppercase}
.hero-stamp-text h1{font-size:21px;margin-top:6px}
.hero-stamp-text p{font-size:13px;color:rgba(255,255,255,.68);margin-top:7px;max-width:520px}
.kpi-row{margin-bottom:26px}.kpi{padding:16px 18px}
.kpi .kpi-num{font-family:var(--f-display);font-size:25px;font-weight:600}
.kpi .kpi-label{font-size:11.5px;color:var(--gray);margin-top:4px;line-height:1.4}
.kpi .kpi-trend{font-family:var(--f-mono);font-size:10.5px;margin-top:8px;display:inline-block;padding:2px 7px;border-radius:10px}
.trend-up{background:var(--green-bg);color:var(--green)}.trend-flat{background:var(--gray-light);color:var(--gray)}.trend-warn{background:var(--amber-bg);color:var(--amber)}
.dash-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:20px;margin-bottom:20px}
.dash-col{display:flex;flex-direction:column;gap:20px}
.list-item{display:flex;gap:13px;padding:13px 16px;border-bottom:1px solid var(--line-soft)}
.list-item:last-child{border-bottom:none}
.list-item-icon{width:30px;height:30px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:var(--red-tint);color:var(--red)}
.list-item-icon svg{width:14px;height:14px}
.list-item-body{flex:1;min-width:0}
.list-item-title{font-size:13px;font-weight:600;line-height:1.35}
.list-item-meta{font-size:11px;color:var(--gray);margin-top:4px;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.alert-row{display:flex;gap:12px;align-items:flex-start;padding:13px 16px;border-left:3px solid var(--amber);background:var(--amber-bg);border-radius:0 var(--radius) var(--radius) 0;margin-bottom:10px}
.alert-row.crit{border-left-color:var(--red);background:var(--red-tint)}
.alert-row svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;color:var(--amber)}.alert-row.crit svg{color:var(--red)}
.alert-row-title{font-size:12.5px;font-weight:600}.alert-row-text{font-size:11.5px;color:var(--gray);margin-top:3px;line-height:1.45}

/* COMUNICADOS */
.filter-row{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
.chip{font-size:12px;font-weight:500;padding:7px 13px;border-radius:20px;border:1px solid var(--line);background:var(--white);color:var(--gray);cursor:pointer}
.chip.active{background:var(--ink);border-color:var(--ink);color:var(--white)}
.com-card{padding:18px 20px;margin-bottom:14px;cursor:pointer}
.com-card-top{display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap}
.com-card-date{font-family:var(--f-mono);font-size:11px;color:var(--gray)}
.com-card h3{font-size:15px;margin-bottom:6px}
.com-card p{font-size:13px;color:var(--gray);line-height:1.55}
.com-card-foot{margin-top:12px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.anexo-link{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--red)}
.anexo-link svg{width:13px;height:13px}

/* POPs */
.lib-toolbar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.lib-toolbar .search{max-width:340px}
.pop-card{display:flex;gap:0;overflow:hidden;margin-bottom:12px}
.pop-card-tab{width:6px;background:var(--red);flex-shrink:0}
.pop-card-body{padding:16px 18px;flex:1;display:flex;gap:16px;align-items:flex-start}
.pop-card-code{font-family:var(--f-mono);font-size:12px;color:var(--gray);background:var(--gray-light);padding:8px 10px;border-radius:var(--radius);text-align:center;flex-shrink:0;min-width:108px}
.pop-card-code .ver{display:block;margin-top:4px;font-weight:600;color:var(--red)}
.pop-card-main{flex:1;min-width:0}.pop-card-main h3{font-size:14.5px;margin-bottom:5px}
.pop-card-main-meta{font-size:11.5px;color:var(--gray);display:flex;gap:12px;flex-wrap:wrap;margin-top:6px}
.pop-card-actions{display:flex;gap:8px;flex-shrink:0;align-self:center}

/* TREINAMENTOS */
.train-card{overflow:hidden}
.train-thumb{height:108px;background:linear-gradient(135deg,var(--ink),#3a3431);display:flex;align-items:center;justify-content:center;position:relative}
.train-thumb svg{width:30px;height:30px;color:rgba(255,255,255,.85)}
.train-thumb .dur{position:absolute;bottom:8px;right:10px;font-family:var(--f-mono);font-size:10.5px;color:#fff;background:rgba(0,0,0,.45);padding:2px 7px;border-radius:10px}
.train-body{padding:16px 18px}.train-body h3{font-size:14px;margin-bottom:7px}
.train-body p{font-size:12.5px;color:var(--gray);line-height:1.55;margin-bottom:12px}
.train-audience{font-size:11px;color:var(--gray);margin-bottom:14px;display:flex;gap:6px;align-items:center}
.train-actions{display:flex;gap:8px;flex-wrap:wrap}.train-actions .btn{flex:1;justify-content:center}

/* PADRONIZAÇÃO */
.table-wrap{overflow-x:auto}
table.std-table{width:100%;border-collapse:collapse;font-size:13px}
table.std-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--gray);padding:11px 16px;border-bottom:1px solid var(--line);font-weight:600}
table.std-table td{padding:13px 16px;border-bottom:1px solid var(--line-soft)}
table.std-table tr:last-child td{border-bottom:none}
.status-dot{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600}
.status-dot::before{content:"";width:7px;height:7px;border-radius:50%}
.status-padronizado::before{background:var(--green)}.status-padronizado{color:var(--green)}
.status-alternativa::before{background:var(--amber)}.status-alternativa{color:var(--amber)}

/* SEGURANÇA */
.lasa-pair{display:flex;align-items:center;gap:14px;padding:15px 18px;margin-bottom:10px;flex-wrap:wrap}
.lasa-word{font-family:var(--f-display);font-weight:700;font-size:16px}
.lasa-word .hl{background:#FCE4A6;padding:0 2px;border-radius:2px}
.lasa-vs{font-family:var(--f-mono);font-size:11px;color:var(--gray)}
.lasa-note{margin-left:auto;font-size:11.5px;color:var(--gray);max-width:280px;text-align:right}
.vigilancia-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.vigilancia-item{display:flex;align-items:center;gap:10px;padding:13px 15px;border-left:3px solid var(--red)}
.vigilancia-item svg{width:16px;height:16px;color:var(--red);flex-shrink:0}
.vigilancia-item span{font-size:13px;font-weight:600}
.check-steps{display:flex;flex-direction:column;gap:0}
.check-step{display:flex;gap:14px;padding:14px 0;border-bottom:1px solid var(--line-soft)}
.check-step:last-child{border-bottom:none}
.check-step-num{font-family:var(--f-mono);font-weight:600;font-size:12px;width:26px;height:26px;border-radius:50%;background:var(--ink);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.check-step-text strong{display:block;font-size:13px;margin-bottom:2px}
.check-step-text span{font-size:12px;color:var(--gray)}

/* FLUXOS */
.flow-acc{margin-bottom:12px}
.flow-acc-head{display:flex;align-items:center;gap:13px;padding:16px 20px;width:100%;background:none;border:none;text-align:left;cursor:pointer}
.flow-acc-head .num{font-family:var(--f-mono);font-size:12px;color:var(--gray)}
.flow-acc-head h3{font-size:14.5px;flex:1}
.flow-acc-head svg{width:9px;height:9px;color:var(--gray);transition:transform .2s;flex-shrink:0}
.flow-acc.open .flow-acc-head svg{transform:rotate(180deg)}
.flow-acc-body{display:none;padding:0 22px 20px}
.flow-acc.open .flow-acc-body{display:block}
.flow-steps{display:flex;gap:0;overflow-x:auto;padding-top:6px;padding-bottom:8px}
.flow-step{display:flex;align-items:flex-start;flex-shrink:0;min-width:168px}
.flow-step-inner{padding-right:18px}
.flow-step-num{font-family:var(--f-mono);font-size:11px;color:var(--white);background:var(--red);width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:8px}
.flow-step-title{font-size:12.5px;font-weight:600;line-height:1.4}
.flow-step-text{font-size:11.5px;color:var(--gray);margin-top:4px;line-height:1.45}
.flow-arrow{color:var(--line);margin:0 2px;padding-top:4px;flex-shrink:0;display:flex;align-items:flex-start}
.flow-arrow svg{width:11px;height:11px;transform:rotate(-90deg)}

/* CONTATO */
.contact-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:22px}
.form-card{padding:24px}
.field{margin-bottom:16px}.field label{display:block;font-size:12px;font-weight:600;margin-bottom:6px}
.field input,.field select,.field textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:var(--radius);font-family:var(--f-body);font-size:13px;background:var(--white);color:var(--ink)}
.field textarea{resize:vertical;min-height:90px}
.field input:focus,.field select:focus,.field textarea:focus{outline:2px solid var(--red);outline-offset:0;border-color:var(--red)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.contact-info-item{display:flex;gap:12px;padding:15px 0;border-bottom:1px solid var(--line-soft)}
.contact-info-item:last-child{border-bottom:none}
.contact-info-item svg{width:16px;height:16px;color:var(--red);flex-shrink:0;margin-top:2px}
.contact-info-item strong{display:block;font-size:12.5px;margin-bottom:2px}
.contact-info-item span{font-size:12px;color:var(--gray);line-height:1.5}
.confirm-box{display:none;align-items:flex-start;gap:12px;background:var(--green-bg);border-left:3px solid var(--green);padding:14px 16px;border-radius:0 var(--radius) var(--radius) 0;margin-bottom:16px}
.confirm-box.show{display:flex}
.confirm-box svg{width:17px;height:17px;color:var(--green);flex-shrink:0;margin-top:1px}
.confirm-box strong{font-size:13px;display:block}.confirm-box span{font-size:12px;color:var(--gray)}

/* MODAL */
.modal-scrim{position:fixed;inset:0;background:rgba(38,35,33,.55);z-index:60;display:none;align-items:center;justify-content:center;padding:24px}
.modal-scrim.show{display:flex}
.modal-box{background:var(--white);border-radius:var(--radius);max-width:600px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-pop)}
.modal-head{padding:22px 24px 16px;border-bottom:1px solid var(--line);display:flex;gap:16px;align-items:flex-start}
.modal-head h2{font-size:17px;flex:1}
.modal-close{background:none;border:none;color:var(--gray);width:28px;height:28px;flex-shrink:0;cursor:pointer}
.modal-close svg{width:18px;height:18px}
.modal-body{padding:20px 24px 26px}

/* ADMIN */
.admin-edit-btn{display:none}
<?php if ($is_admin_user): ?>
.admin-edit-btn{display:inline-flex}
<?php endif; ?>

/* SIDEBAR ADMIN BLOCK */
.sidebar-admin{padding:12px 16px 18px;border-top:1px solid rgba(255,255,255,.12)}
.sidebar-admin-label{font-size:11px;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:5px;margin-bottom:10px;flex-wrap:wrap}
.sidebar-admin-label a{color:rgba(255,255,255,.8);font-weight:600}
.sidebar-admin-label a:hover{color:var(--white)}
.sidebar-admin .btn-block{font-size:12px}

/* RESPONSIVO */
@media(max-width:980px){.grid-4{grid-template-columns:repeat(2,1fr)}.grid-3{grid-template-columns:repeat(2,1fr)}.dash-grid{grid-template-columns:1fr}.contact-grid{grid-template-columns:1fr}}
@media(max-width:760px){
  .sidebar{transform:translateX(-100%);width:270px}
  .sidebar.open{transform:translateX(0);box-shadow:var(--shadow-pop)}
  .main{margin-left:0}.topbar{display:flex}.topstrip{padding:14px 18px}
  .content{padding:22px 18px 50px}
  .grid-2,.grid-3,.grid-4{grid-template-columns:1fr}
  .vigilancia-grid{grid-template-columns:1fr}.field-row{grid-template-columns:1fr}
  .hero-stamp{flex-direction:column;align-items:flex-start}
  .pop-card-body{flex-direction:column;gap:12px}.pop-card-actions{align-self:flex-start}
  .sidebar-scrim.show{display:block;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:35}
  .page-head{flex-direction:column;align-items:flex-start}
}
</style>
</head>
<body>
<div class="app">

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="selo selo-sm"><span class="selo-texto">Farmácia · Uso Seguro</span></div>
    <div class="sidebar-brand-text">
      <strong>Farmácia Hospitalar</strong>
      <span>Hospital Hospital de Rede Privada</span>
    </div>
  </div>
  <nav class="nav" id="nav"></nav>
  <?php if ($is_admin_user): ?>
  <div class="sidebar-admin">
    <div class="sidebar-admin-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><path d="M12 3l8 3v6c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V6l8-3z"/></svg>
      Administrador &nbsp;·&nbsp; <a href="<?= $admin_url ?>">Painel WP</a>
    </div>
    <button class="btn btn-sm btn-primary btn-block" id="btnNovoConteudo">+ Novo conteúdo</button>
  </div>
  <?php else: ?>
  <div class="sidebar-foot">
    Portal de uso interno.<br>Farmácia Hospitalar · HSL Bauru
  </div>
  <?php endif; ?>
</aside>
<div class="sidebar-scrim" id="sidebarScrim"></div>

<div class="main">
  <div class="topbar">
    <button id="menuBtn" aria-label="Abrir menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
    <div class="selo" style="--size:32px"><span class="selo-texto" style="font-size:4px">Farmácia</span></div>
    <span class="topbar-title">Farmácia Hospitalar</span>
  </div>

  <div class="topstrip">
    <div class="search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input id="globalSearch" placeholder="Buscar POPs, comunicados, medicamentos…" autocomplete="off">
    </div>
    <div class="topstrip-meta">
      <span class="pill-status"><span>Sistemas operacionais</span></span>
    </div>
  </div>

  <div class="content" id="content"></div>
</div>
</div><!-- .app -->

<!-- MODAL COMUNICADO -->
<div class="modal-scrim" id="modalScrim">
  <div class="modal-box">
    <div class="modal-head">
      <h2 id="modalTitle"></h2>
      <button class="modal-close" id="modalClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<!-- MODAL ADMIN FORM -->
<div class="modal-scrim" id="adminModalScrim">
  <div class="modal-box" style="max-width:680px">
    <div class="modal-head">
      <h2 id="adminModalTitle">Novo conteúdo</h2>
      <button class="modal-close" id="adminModalClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body" id="adminModalBody"></div>
  </div>
</div>

<script>
/* ========== DADOS DO WORDPRESS ========== */
const IS_ADMIN      = <?= $is_admin_user ? 'true' : 'false' ?>;
const INITIAL_VIEW  = '<?= $initial_view ?>';
const PAGE_URLS     = <?= $json_page_urls ?>;
const REST_URL   = '<?= $rest_url ?>';
const WP_REST    = '<?= $wp_rest ?>';
const NONCE      = '<?= $nonce ?>';
const ADMIN_URL  = '<?= $admin_url ?>';

let COMUNICADOS   = <?= $json_comunicados ?>;
let POPS          = <?= $json_pops ?>;
let TREINAMENTOS  = <?= $json_treinamentos ?>;
let MEDICAMENTOS  = <?= $json_medicamentos ?>;
let LASA_PARES    = <?= $json_lasa ?>;
let ALTA_VIGILANCIA = <?= $json_alta ?>;
let FLUXOS        = <?= $json_fluxos ?>;

/* ========== ÍCONES ========== */
const ICONS = {
  home:'<path d="M4 11.5L12 4l8 7.5"/><path d="M6 10v9h12v-9"/><path d="M10 19v-5h4v5"/>',
  megaphone:'<path d="M3 11v3a1 1 0 001 1h2l4 4V6L6 10H4a1 1 0 00-1 1z"/><path d="M10 7l8-3v17l-8-3"/>',
  book:'<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M4 5.5A2.5 2.5 0 016.5 3H20v17H6.5A2.5 2.5 0 014 17.5v-12z"/>',
  cap:'<path d="M2 9l10-4 10 4-10 4-10-4z"/><path d="M6 11.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-4.5"/>',
  pill:'<path d="M5 13.5L13.5 5a4.5 4.5 0 016.36 6.36L11.36 19.86A4.5 4.5 0 015 13.5z"/><path d="M9 9l6 6"/>',
  shield:'<path d="M12 3l8 3v6c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V6l8-3z"/>',
  flow:'<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="12" cy="18" r="2.2"/><path d="M7 7l3.5 9M17 7l-3.5 9"/>',
  mail:'<path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>',
  chev:'<path d="M6 9l6 6 6-6"/>',
  paperclip:'<path d="M8 12l6-6a3 3 0 014.2 4.2l-8 8a5 5 0 01-7-7l7-7"/>',
  alert:'<path d="M12 3l9 16H3l9-16z"/><path d="M12 10v4"/><circle cx="12" cy="17" r="0.6"/>',
  check:'<path d="M5 13l4 4L19 7"/>',
  bell:'<path d="M9 18a3 3 0 006 0"/><path d="M6 11a6 6 0 1112 0v3.5l1.5 2.5h-15L6 14.5V11z"/>',
  download:'<path d="M12 4v11"/><path d="M7 11l5 5 5-5"/><path d="M5 19h14"/>',
  film:'<path d="M4 5h16v14H4z"/><path d="M4 9h16M4 15h16M9 5v4M9 15v4"/>',
  pin:'<path d="M12 21s7-6.2 7-11.5A7 7 0 105 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.3"/>',
  phone:'<path d="M5 4h3l2 5-2.5 2a11 11 0 005.5 5.5l2-2.5 5 2v3a1 1 0 01-1 1A16 16 0 014 5a1 1 0 011-1z"/>',
  clock:'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
  send:'<path d="M3 11l18-8-8 18-2.5-7L3 11z"/>',
  edit:'<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/>',
  plus:'<path d="M12 5v14M5 12h14"/>',
  trash:'<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/>',
};
function ic(name,cls=''){return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${ICONS[name]||''}</svg>`;}

/* ========== HELPERS ========== */
const NAV_ITEMS = [
  {id:'home',label:'Início',icon:'home'},
  {id:'comunicados',label:'Comunicados',icon:'megaphone',badge:()=>COMUNICADOS.length},
  {id:'pops',label:'POPs e Protocolos',icon:'book',badge:()=>POPS.length},
  {id:'treinamentos',label:'Treinamentos',icon:'cap'},
  {id:'padronizacao',label:'Padronização',icon:'pill'},
  {id:'seguranca',label:'Segurança do Paciente',icon:'shield'},
  {id:'fluxos',label:'Fluxos e Processos',icon:'flow'},
  {id:'contato',label:'Contato',icon:'mail'},
];

let currentView='home';
let popFiltro={cat:'todas',q:''};
let comFiltro={cat:'todas'};

const TIPO_LABELS={protocolo:'Protocolo',padronizacao:'Padronização',desabastecimento:'Desabastecimento',alerta:'Alerta sanitário',orientacao:'Orientação institucional'};
function catTagClass(t){return {protocolo:'tag-protocolo',padronizacao:'tag-padronizacao',desabastecimento:'tag-desabastecimento',alerta:'tag-alerta',orientacao:'tag-orientacao'}[t]||'tag-orientacao';}
function tipoLabel(t){return TIPO_LABELS[t]||t||'Comunicado';}

function adminBtn(label, onclick, icon='edit'){
  if(!IS_ADMIN) return '';
  return `<button class="btn btn-sm admin-edit-btn" onclick="${onclick}">${ic(icon)} ${label}</button>`;
}

/* ========== VIEWS ========== */
function renderNav(){
  document.getElementById('nav').innerHTML=NAV_ITEMS.map(item=>`
    <button class="nav-item ${item.id===currentView?'active':''}" data-view="${item.id}">
      ${ic(item.icon)}<span>${item.label}</span>
      ${item.badge?`<span class="badge">${item.badge()}</span>`:''}
    </button>`).join('');
  document.querySelectorAll('.nav-item').forEach(b=>{
    b.addEventListener('click',()=>{navigate(b.dataset.view);closeMobileMenu();});
  });
}

function renderHome(){
  const alertas = COMUNICADOS.filter(c=>c.tipo==='alerta'||c.tipo==='desabastecimento').slice(0,3);
  return `
    <div class="page-head">
      <div><span class="eyebrow">Painel principal</span><h1>Bem-vindo ao portal da Farmácia Hospitalar</h1>
        <p>Acesso rápido a comunicados, protocolos e indicadores da assistência farmacêutica do Hospital Hospital de Rede Privada.</p></div>
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
      <div class="card kpi"><div class="kpi-num">${COMUNICADOS.length}</div><div class="kpi-label">Comunicados publicados</div></div>
      <div class="card kpi"><div class="kpi-num">${POPS.length}</div><div class="kpi-label">POPs na biblioteca</div></div>
      <div class="card kpi"><div class="kpi-num">${TREINAMENTOS.length}</div><div class="kpi-label">Treinamentos disponíveis</div></div>
      <div class="card kpi"><div class="kpi-num">${MEDICAMENTOS.length}</div><div class="kpi-label">Medicamentos padronizados</div></div>
    </div>
    <div class="dash-grid">
      <div class="dash-col">
        <div class="card" style="padding:18px 18px 8px">
          <div class="section-title">${ic('megaphone')} Comunicados recentes <span class="ver-tudo" data-go="comunicados">Ver todos</span></div>
          ${COMUNICADOS.slice(0,4).map(c=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('megaphone')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${c.titulo}</div>
                <div class="list-item-meta"><span class="tag ${catTagClass(c.tipo)}">${tipoLabel(c.tipo)}</span><span class="mono">${c.data}</span></div>
              </div>
            </div>`).join('')||'<div class="empty">Nenhum comunicado publicado.</div>'}
        </div>
        <div class="card" style="padding:18px 18px 8px;margin-top:0">
          <div class="section-title">${ic('book')} POPs recentes <span class="ver-tudo" data-go="pops">Ver biblioteca</span></div>
          ${POPS.slice(0,4).map(p=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('book')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${p.titulo}</div>
                <div class="list-item-meta"><span class="mono">${p.codigo||''} ${p.versao?'· '+p.versao:''}</span></div>
              </div>
            </div>`).join('')||'<div class="empty">Nenhum POP publicado.</div>'}
        </div>
      </div>
      <div class="dash-col">
        <div class="card" style="padding:18px">
          <div class="section-title">${ic('alert')} Alertas de segurança</div>
          ${alertas.length?alertas.map(c=>`
            <div class="alert-row ${c.tipo==='alerta'?'crit':''}">${ic('alert')}
              <div><div class="alert-row-title">${c.titulo}</div><div class="alert-row-text">${c.resumo}</div></div>
            </div>`).join(''):'<div class="empty">Sem alertas ativos.</div>'}
        </div>
        <div class="card" style="padding:18px 18px 8px;margin-top:0">
          <div class="section-title">${ic('cap')} Treinamentos <span class="ver-tudo" data-go="treinamentos">Ver todos</span></div>
          ${TREINAMENTOS.slice(0,2).map(t=>`
            <div class="list-item">
              <div class="list-item-icon">${ic('cap')}</div>
              <div class="list-item-body">
                <div class="list-item-title">${t.titulo}</div>
                <div class="list-item-meta"><span class="mono">${t.duracao||''}</span><span>${t.publico||''}</span></div>
              </div>
            </div>`).join('')||'<div class="empty">Nenhum treinamento publicado.</div>'}
        </div>
      </div>
    </div>`;
}

function renderComunicados(){
  const cats=[{id:'todas',label:'Todas'},{id:'protocolo',label:'Protocolo'},{id:'padronizacao',label:'Padronização'},{id:'desabastecimento',label:'Desabastecimento'},{id:'alerta',label:'Alerta sanitário'},{id:'orientacao',label:'Orientação'}];
  const lista=COMUNICADOS.filter(c=>comFiltro.cat==='todas'||c.tipo===comFiltro.cat);
  return `
    <div class="page-head">
      <div><span class="eyebrow">Comunicação oficial</span><h1>Comunicados da Farmácia</h1>
        <p>Publicações sobre protocolos, padronização, desabastecimentos e alertas sanitários.</p></div>
      <div>${IS_ADMIN?`<button class="btn btn-primary" onclick="openAdminForm('comunicado')"> ${ic('plus')} Novo comunicado</button>`:''}</div>
    </div>
    <div class="filter-row">
      ${cats.map(c=>`<button class="chip ${comFiltro.cat===c.id?'active':''}" data-comcat="${c.id}">${c.label}</button>`).join('')}
    </div>
    <div id="comList">
      ${lista.length?lista.map(c=>`
        <div class="card com-card" data-comid="${c.id}">
          <div class="com-card-top">
            <span class="tag ${catTagClass(c.tipo)}">${tipoLabel(c.tipo)}</span>
            <span class="com-card-date mono">${c.data}</span>
            ${IS_ADMIN?`<span style="margin-left:auto">${adminBtn('Editar','openAdminForm(\'comunicado\','+c.id+')')}</span>`:''}
          </div>
          <h3>${c.titulo}</h3>
          <p>${c.resumo}</p>
          <div class="com-card-foot">
            <button class="btn btn-ghost" data-comid="${c.id}">Ler comunicado completo →</button>
            ${c.anexo?`<a href="${c.anexo}" class="anexo-link" target="_blank">${ic('paperclip')} Anexo</a>`:''}
          </div>
        </div>`).join(''):'<div class="empty">Nenhum comunicado nesta categoria.</div>'}
    </div>`;
}

function renderPops(){
  const lista=POPS.filter(p=>{
    const mc=popFiltro.cat==='todas'||p.categoria===popFiltro.cat;
    const q=popFiltro.q.trim().toLowerCase();
    return mc&&(!q||p.titulo.toLowerCase().includes(q)||(p.codigo||'').toLowerCase().includes(q));
  });
  const cats=[{id:'todas',label:'Todas'},{id:'dispensacao',label:'Dispensação'},{id:'armazenamento',label:'Armazenamento'},{id:'cadeia-fria',label:'Cadeia fria'},{id:'alta-vigilancia',label:'Alta vigilância'},{id:'antimicrobianos',label:'Antimicrobianos'},{id:'fracionamento',label:'Fracionamento'},{id:'farmacotecnica',label:'Farmacotécnica'}];
  return `
    <div class="page-head">
      <div><span class="eyebrow">Documentação técnica</span><h1>Biblioteca de POPs e Protocolos</h1>
        <p>Procedimentos Operacionais Padrão vigentes, com controle de versão.</p></div>
      <div>${IS_ADMIN?`<button class="btn btn-primary" onclick="openAdminForm('pop')">${ic('plus')} Novo POP</button>`:''}</div>
    </div>
    <div class="lib-toolbar">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input id="popSearch" placeholder="Buscar por título ou código…" value="${popFiltro.q}">
      </div>
    </div>
    <div class="filter-row">${cats.map(c=>`<button class="chip ${popFiltro.cat===c.id?'active':''}" data-popcat="${c.id}">${c.label}</button>`).join('')}</div>
    <div id="popList">
      ${lista.length?lista.map(p=>`
        <div class="card pop-card">
          <div class="pop-card-tab"></div>
          <div class="pop-card-body">
            <div class="pop-card-code mono">${p.codigo||'—'}<span class="ver">${p.versao||''}</span></div>
            <div class="pop-card-main">
              <h3>${p.titulo}</h3>
              <div class="pop-card-main-meta">
                <span class="tag tag-orientacao">${p.categoria||''}</span>
                <span class="mono">Atualizado em ${p.data}</span>
              </div>
            </div>
            <div class="pop-card-actions">
              ${p.arquivo?`<a href="${p.arquivo}" class="btn btn-primary" target="_blank">${ic('download')} PDF</a>`:`<span class="btn" style="opacity:.4">${ic('download')} PDF</span>`}
              ${IS_ADMIN?adminBtn('Editar','openAdminForm(\'pop\','+p.id+')'):''}
            </div>
          </div>
        </div>`).join(''):'<div class="empty">Nenhum POP encontrado.</div>'}
    </div>`;
}

function renderTreinamentos(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Educação continuada</span><h1>Treinamentos da Farmácia Hospitalar</h1>
        <p>Capacitações com vídeo, material de apoio e avaliação.</p></div>
      <div>${IS_ADMIN?`<button class="btn btn-primary" onclick="openAdminForm('treinamento')">${ic('plus')} Novo treinamento</button>`:''}</div>
    </div>
    <div class="grid grid-2">
      ${TREINAMENTOS.length?TREINAMENTOS.map(t=>`
        <div class="card train-card">
          <div class="train-thumb">${ic('film')}<span class="dur mono">${t.duracao||''}</span></div>
          <div class="train-body">
            <h3>${t.titulo}</h3>
            <p>${t.descricao||''}</p>
            <div class="train-audience">${ic('cap')} Público-alvo: ${t.publico||'Todos'}</div>
            <div class="train-actions">
              ${t.video?`<a href="${t.video}" class="btn btn-primary" target="_blank">${ic('film')} Assistir</a>`:''}
              ${t.pdf?`<a href="${t.pdf}" class="btn" target="_blank">${ic('download')} Material</a>`:''}
              ${t.quiz?`<a href="${t.quiz}" class="btn" target="_blank">${ic('check')} Avaliação</a>`:''}
              ${IS_ADMIN?adminBtn('Editar','openAdminForm(\'treinamento\','+t.id+')'):''}
            </div>
          </div>
        </div>`).join(''):'<div class="empty">Nenhum treinamento publicado.</div>'}
    </div>`;
}

function renderPadronizacao(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Padronização institucional</span><h1>Padronização de Medicamentos</h1>
        <p>Lista vigente aprovada pela Comissão de Farmácia e Terapêutica.</p></div>
      <div>${IS_ADMIN?`<button class="btn btn-primary" onclick="openAdminForm('medicamento')">${ic('plus')} Novo medicamento</button>`:''}</div>
    </div>
    <div class="card" style="margin-bottom:20px">
      <div class="table-wrap">
        <table class="std-table">
          <thead><tr><th>Medicamento</th><th>Classe</th><th>Apresentação</th><th>Status</th>${IS_ADMIN?'<th></th>':''}</tr></thead>
          <tbody>
            ${MEDICAMENTOS.length?MEDICAMENTOS.map(m=>`
              <tr>
                <td><strong>${m.titulo}</strong></td>
                <td>${m.classe||'—'}</td>
                <td>${m.apresentacao||'—'}</td>
                <td><span class="status-dot status-${m.status||'padronizado'}">${m.status==='alternativa'?'Alternativa em uso':'Padronizado'}</span></td>
                ${IS_ADMIN?`<td>${adminBtn('Editar','openAdminForm(\'medicamento\','+m.id+')')}</td>`:''}
              </tr>`).join(''):`<tr><td colspan="${IS_ADMIN?5:4}" class="empty">Nenhum medicamento cadastrado.</td></tr>`}
          </tbody>
        </table>
      </div>
    </div>`;
}

function renderSeguranca(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Segurança do paciente</span><h1>Segurança do Paciente — Farmácia</h1>
        <p>Medicamentos LASA, alta vigilância e prevenção de erros de medicação.</p></div>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <div class="section-title" style="margin-bottom:0">${ic('alert')} Medicamentos LASA</div>
      ${IS_ADMIN?`<button class="btn btn-sm btn-primary" onclick="openAdminForm('lasa')">${ic('plus')} Novo par LASA</button>`:''}
    </div>
    <div style="margin-bottom:26px">
      ${LASA_PARES.length?LASA_PARES.map(l=>`
        <div class="card lasa-pair">
          <span class="lasa-word">${l.med_a||'—'}</span>
          <span class="lasa-vs">vs</span>
          <span class="lasa-word">${l.med_b||'—'}</span>
          <span class="lasa-note">${l.nota||''}</span>
          ${IS_ADMIN?adminBtn('','openAdminForm(\'lasa\','+l.id+')'):''}
        </div>`).join(''):'<div class="card empty">Nenhum par LASA cadastrado.</div>'}
    </div>
    <div class="grid grid-2" style="margin-bottom:26px">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
          <div class="section-title" style="margin-bottom:0">${ic('shield')} Medicamentos de alta vigilância</div>
          ${IS_ADMIN?`<button class="btn btn-sm btn-primary" onclick="openAdminForm('altaVigilancia')">${ic('plus')}</button>`:''}
        </div>
        <div class="vigilancia-grid">
          ${ALTA_VIGILANCIA.length?ALTA_VIGILANCIA.map(v=>`
            <div class="card vigilancia-item">${ic('alert')}<span>${v.titulo}</span></div>`).join(''):'<div class="empty">Nenhum item cadastrado.</div>'}
        </div>
      </div>
      <div>
        <div class="section-title">${ic('check')} Protocolo de dupla checagem</div>
        <div class="card" style="padding:6px 20px">
          <div class="check-steps">
            <div class="check-step"><div class="check-step-num">1</div><div class="check-step-text"><strong>Identificação do paciente</strong><span>Confirmar nome e data de nascimento com a pulseira.</span></div></div>
            <div class="check-step"><div class="check-step-num">2</div><div class="check-step-text"><strong>Confirmação do medicamento</strong><span>Verificar nome, concentração, apresentação e validade.</span></div></div>
            <div class="check-step"><div class="check-step-num">3</div><div class="check-step-text"><strong>Checagem da prescrição</strong><span>Confrontar dose, via e horário com a prescrição vigente.</span></div></div>
            <div class="check-step"><div class="check-step-num">4</div><div class="check-step-text"><strong>Assinatura dos dois profissionais</strong><span>Ambos assinam o registro antes da administração.</span></div></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card" style="padding:20px;display:flex;gap:16px;align-items:center;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <div class="section-title" style="margin-bottom:6px">${ic('bell')} Identificou um erro ou evento adverso?</div>
        <p style="font-size:12.5px;color:var(--gray)">A notificação é essencial para a melhoria contínua. Não é necessário se identificar.</p>
      </div>
      <button class="btn btn-primary" data-go="contato">${ic('send')} Notificar evento</button>
    </div>`;
}

function renderFluxos(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Processos operacionais</span><h1>Fluxos e Processos da Farmácia</h1>
        <p>Etapas padronizadas para solicitações, devoluções e atendimentos especiais.</p></div>
      <div>${IS_ADMIN?`<button class="btn btn-primary" onclick="openAdminForm('fluxo')">${ic('plus')} Novo fluxo</button>`:''}</div>
    </div>
    <div id="flowList">
      ${FLUXOS.length?FLUXOS.map((f,i)=>`
        <div class="card flow-acc ${i===0?'open':''}" data-flow="${i}">
          <button class="flow-acc-head" data-flowtoggle="${i}">
            <span class="num mono">0${i+1}</span>
            <h3>${f.titulo}</h3>
            ${IS_ADMIN?adminBtn('Editar','openAdminForm(\'fluxo\','+f.id+')'):''}
          </button>
          <div class="flow-acc-body">
            <div class="flow-steps">
              ${(f.passos||[]).map((p,j)=>`
                <div class="flow-step">
                  <div class="flow-step-inner">
                    <div class="flow-step-num mono">${j+1}</div>
                    <div class="flow-step-title">${p.t}</div>
                    <div class="flow-step-text">${p.d}</div>
                  </div>
                </div>
                ${j<(f.passos.length-1)?'<div class="flow-arrow">'+ic('chev')+'</div>':''}`).join('')||'<div class="empty">Sem passos cadastrados.</div>'}
            </div>
          </div>
        </div>`).join(''):'<div class="card empty">Nenhum fluxo cadastrado.</div>'}
    </div>`;
}

function renderContato(){
  return `
    <div class="page-head">
      <div><span class="eyebrow">Canal direto</span><h1>Contato e Solicitações</h1>
        <p>Solicite medicamentos, tire dúvidas ou notifique problemas. O envio pode ser anônimo.</p></div>
    </div>
    <div class="contact-grid">
      <div class="card form-card">
        <div class="confirm-box" id="confirmBox">
          ${ic('check')}
          <div><strong>Solicitação registrada com sucesso!</strong><span>Sua mensagem foi recebida pela Farmácia Hospitalar.</span></div>
        </div>
        <form id="contactForm">
          <div class="field-row">
            <div class="field"><label>Nome (opcional)</label><input id="cNome" placeholder="Deixe em branco para envio anônimo"></div>
            <div class="field"><label>Setor</label><input id="cSetor" placeholder="Ex.: UTI Adulto"></div>
          </div>
          <div class="field">
            <label>Tipo de solicitação</label>
            <select id="cTipo">
              <option>Solicitação de medicamento não padronizado</option>
              <option>Dúvida técnica</option>
              <option>Sugestão</option>
              <option>Notificação de erro / evento adverso</option>
              <option>Outros</option>
            </select>
          </div>
          <div class="field"><label>Mensagem</label><textarea id="cMensagem" required placeholder="Descreva sua solicitação com o máximo de detalhes…"></textarea></div>
          <button class="btn btn-primary btn-block" type="submit">${ic('send')} Enviar solicitação</button>
        </form>
      </div>
      <div class="card" style="padding:22px 24px">
        <div class="section-title" style="margin-bottom:16px">Canais diretos</div>
        <div class="contact-info-item">${ic('phone')}<div><strong>Ramal da Farmácia Central</strong><span>Ramal 4521 — dias úteis, 7h às 19h</span></div></div>
        <div class="contact-info-item">${ic('mail')}<div><strong>E-mail institucional</strong><span>farmacia.hospitalar@hospitalsantalucia.exemplo</span></div></div>
        <div class="contact-info-item">${ic('clock')}<div><strong>Plantão farmacêutico</strong><span>24 horas, todos os dias — ramal urgência 4599</span></div></div>
        <div class="contact-info-item">${ic('pin')}<div><strong>Localização</strong><span>Farmácia Central — Bloco A, térreo</span></div></div>
      </div>
    </div>`;
}

/* ========== ADMIN FORMS ========== */
function openAdminForm(type, id=null){
  const titles={comunicado:'Comunicado',pop:'POP/Protocolo',treinamento:'Treinamento',medicamento:'Medicamento',lasa:'Par LASA',altaVigilancia:'Alta Vigilância',fluxo:'Fluxo de Processo'};
  document.getElementById('adminModalTitle').textContent=(id?'Editar ':'Novo ')+titles[type];

  let data={};
  if(id){
    if(type==='comunicado') data=COMUNICADOS.find(x=>x.id==id)||{};
    if(type==='pop') data=POPS.find(x=>x.id==id)||{};
    if(type==='treinamento') data=TREINAMENTOS.find(x=>x.id==id)||{};
    if(type==='medicamento') data=MEDICAMENTOS.find(x=>x.id==id)||{};
    if(type==='lasa') data=LASA_PARES.find(x=>x.id==id)||{};
    if(type==='altaVigilancia') data=ALTA_VIGILANCIA.find(x=>x.id==id)||{};
    if(type==='fluxo') data=FLUXOS.find(x=>x.id==id)||{};
  }

  const f=(label,name,val='',tag='input',opts='')=>
    `<div class="field"><label>${label}</label>${tag==='textarea'?`<textarea name="${name}">${val}</textarea>`:tag==='select'?`<select name="${name}">${opts}</select>`:`<input name="${name}" value="${val||''}">`}</div>`;

  let body='';
  if(type==='comunicado'){
    const tipos=[{v:'protocolo',l:'Protocolo'},{v:'padronizacao',l:'Padronização'},{v:'desabastecimento',l:'Desabastecimento'},{v:'alerta',l:'Alerta sanitário'},{v:'orientacao',l:'Orientação institucional'}];
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Título','titulo',data.titulo)}
      ${f('Tipo','tipo','',`select`,tipos.map(t=>`<option value="${t.v}" ${data.tipo===t.v?'selected':''}>${t.l}</option>`).join(''))}
      ${f('Resumo','resumo',data.resumo,'textarea')}
      ${f('Corpo (HTML ou texto)','corpo',data.corpo||'','textarea')}
      ${f('URL do Anexo (PDF)','anexo',data.anexo)}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('comunicado',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='pop'){
    const cats=['dispensacao','armazenamento','cadeia-fria','alta-vigilancia','antimicrobianos','fracionamento','farmacotecnica','farmacopeia'];
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Título','titulo',data.titulo)}
      ${f('Código (ex: POP-FAR-001)','codigo',data.codigo)}
      ${f('Versão (ex: v2.0)','versao',data.versao)}
      ${f('Categoria','categoria','',`select`,cats.map(c=>`<option value="${c}" ${data.categoria===c?'selected':''}>${c}</option>`).join(''))}
      ${f('URL do Arquivo PDF','arquivo',data.arquivo)}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('pop_protocolo',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='treinamento'){
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Título','titulo',data.titulo)}
      ${f('Descrição','descricao',data.descricao,'textarea')}
      ${f('Público-alvo','publico',data.publico)}
      ${f('Duração (ex: 25 min)','duracao',data.duracao)}
      ${f('URL do Vídeo (YouTube/Drive)','video',data.video)}
      ${f('URL do PDF (material didático)','pdf',data.pdf)}
      ${f('URL do Questionário','quiz',data.quiz)}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('treinamento',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='medicamento'){
    const statuses=['padronizado','alternativa'];
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Nome do Medicamento','titulo',data.titulo)}
      ${f('Classe Farmacológica','classe',data.classe)}
      ${f('Apresentação','apresentacao',data.apresentacao)}
      ${f('Status','status','',`select`,statuses.map(s=>`<option value="${s}" ${(data.status||'padronizado')===s?'selected':''}>${s==='padronizado'?'Padronizado':'Alternativa em uso'}</option>`).join(''))}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('med_padronizado',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='lasa'){
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Medicamento A','med_a',data.med_a)}
      ${f('Medicamento B','med_b',data.med_b)}
      ${f('Observação de segurança','nota',data.nota,'textarea')}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('lasa_par',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='altaVigilancia'){
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Nome do medicamento / grupo','titulo',data.titulo)}
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('alta_vigilancia',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  } else if(type==='fluxo'){
    const passosJson = JSON.stringify(data.passos||[{t:'',d:''}]);
    body=`<form class="admin-form" data-type="${type}" data-id="${id||''}">
      ${f('Título do fluxo','titulo',data.titulo)}
      <div class="field"><label>Passos do fluxo (JSON: [{"t":"Título","d":"Descrição"},…])</label>
        <textarea name="passos" style="min-height:140px;font-family:var(--f-mono);font-size:12px">${passosJson}</textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        ${id?`<button type="button" class="btn" style="color:var(--red);border-color:var(--red)" onclick="deletePost('fluxo_processo',${id})">Excluir</button>`:''}
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div></form>`;
  }

  document.getElementById('adminModalBody').innerHTML=body;
  document.getElementById('adminModalScrim').classList.add('show');

  document.querySelector('.admin-form')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const fd=new FormData(e.target);
    const vals=Object.fromEntries(fd.entries());
    await savePost(type,id,vals);
  });
}

/* ========== CRUD API ========== */
async function savePost(type,id,vals){
  const res=await fetch(REST_URL+'save',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({type,id:id||0,fields:vals})
  });
  if(res.ok){
    closeAdminModal();
    setTimeout(()=>location.reload(),200);
  } else {
    const err=await res.json().catch(()=>({}));
    alert('Erro ao salvar: '+(err.message||res.status));
  }
}

async function deletePost(postType,id){
  if(!confirm('Excluir este item?')) return;
  const res=await fetch(REST_URL+'delete',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({id})
  });
  if(res.ok){closeAdminModal();setTimeout(()=>location.reload(),200);}
  else alert('Erro ao excluir.');
}

function closeAdminModal(){document.getElementById('adminModalScrim').classList.remove('show');}

/* ========== ROTEAMENTO ========== */
const RENDERERS={home:renderHome,comunicados:renderComunicados,pops:renderPops,treinamentos:renderTreinamentos,padronizacao:renderPadronizacao,seguranca:renderSeguranca,fluxos:renderFluxos,contato:renderContato};

function navigate(view,pushState=true){
  if(!RENDERERS[view]) return;
  currentView=view;
  document.getElementById('content').innerHTML=`<div class="view active">${RENDERERS[view]()}</div>`;
  renderNav();
  bindViewEvents(view);
  window.scrollTo(0,0);
  if(pushState){
    const url=PAGE_URLS[view]||PAGE_URLS.home;
    if(location.href!==url) history.pushState({view},document.title,url);
  }
}

function bindViewEvents(view){
  document.querySelectorAll('[data-go]').forEach(el=>el.addEventListener('click',()=>navigate(el.dataset.go)));
  if(view==='comunicados'){
    document.querySelectorAll('[data-comcat]').forEach(el=>el.addEventListener('click',()=>{comFiltro.cat=el.dataset.comcat;navigate('comunicados');}));
    document.querySelectorAll('[data-comid]').forEach(el=>el.addEventListener('click',()=>openComunicado(el.dataset.comid)));
  }
  if(view==='pops'){
    document.querySelectorAll('[data-popcat]').forEach(el=>el.addEventListener('click',()=>{popFiltro.cat=el.dataset.popcat;navigate('pops');}));
    const inp=document.getElementById('popSearch');
    if(inp) inp.addEventListener('input',()=>{popFiltro.q=inp.value;navigate('pops');const ni=document.getElementById('popSearch');ni&&ni.focus();});
  }
  if(view==='fluxos'){
    document.querySelectorAll('[data-flowtoggle]').forEach(el=>el.addEventListener('click',()=>el.closest('.flow-acc').classList.toggle('open')));
  }
  if(view==='contato'){
    document.getElementById('contactForm')?.addEventListener('submit',async(e)=>{
      e.preventDefault();
      const body={nome:document.getElementById('cNome').value,setor:document.getElementById('cSetor').value,tipo:document.getElementById('cTipo').value,mensagem:document.getElementById('cMensagem').value};
      const res=await fetch(REST_URL+'contato',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
      if(res.ok){document.getElementById('confirmBox').classList.add('show');e.target.reset();document.getElementById('confirmBox').scrollIntoView({behavior:'smooth',block:'center'});}
      else alert('Erro ao enviar. Tente novamente.');
    });
  }
}

function openComunicado(id){
  const c=COMUNICADOS.find(x=>x.id==id);
  if(!c) return;
  document.getElementById('modalTitle').textContent=c.titulo;
  document.getElementById('modalBody').innerHTML=`
    <div class="com-card-top" style="margin-bottom:14px">
      <span class="tag ${catTagClass(c.tipo)}">${tipoLabel(c.tipo)}</span>
      <span class="com-card-date mono">${c.data}</span>
    </div>
    <div style="font-size:13.5px;line-height:1.7">${c.corpo||c.resumo||''}</div>
    ${c.anexo?`<div style="margin-top:16px"><a href="${c.anexo}" class="anexo-link" target="_blank">${ic('paperclip')} Baixar anexo</a></div>`:''}`;
  document.getElementById('modalScrim').classList.add('show');
}

/* ========== INTERAÇÕES GLOBAIS ========== */
function openMobileMenu(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarScrim').classList.add('show');}
function closeMobileMenu(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarScrim').classList.remove('show');}
document.getElementById('menuBtn')?.addEventListener('click',openMobileMenu);
document.getElementById('sidebarScrim').addEventListener('click',closeMobileMenu);
document.getElementById('modalClose').addEventListener('click',()=>document.getElementById('modalScrim').classList.remove('show'));
document.getElementById('modalScrim').addEventListener('click',(e)=>{if(e.target.id==='modalScrim')document.getElementById('modalScrim').classList.remove('show');});
document.getElementById('adminModalClose').addEventListener('click',closeAdminModal);
document.getElementById('adminModalScrim').addEventListener('click',(e)=>{if(e.target.id==='adminModalScrim')closeAdminModal();});
document.addEventListener('keydown',(e)=>{if(e.key==='Escape'){document.getElementById('modalScrim').classList.remove('show');closeAdminModal();}});
document.getElementById('globalSearch')?.addEventListener('keydown',(e)=>{
  if(e.key==='Enter'){const q=e.target.value.trim();if(!q)return;popFiltro={cat:'todas',q};navigate('pops');}
});
document.getElementById('btnNovoConteudo')?.addEventListener('click',()=>{
  const choices=['comunicado','pop','treinamento','medicamento','lasa','altaVigilancia','fluxo'];
  const labels=['Comunicado','POP/Protocolo','Treinamento','Medicamento Padronizado','Par LASA','Alta Vigilância','Fluxo de Processo'];
  const idx=choices.indexOf({home:'comunicado',comunicados:'comunicado',pops:'pop',treinamentos:'treinamento',padronizacao:'medicamento',seguranca:'lasa',fluxos:'fluxo',contato:'comunicado'}[currentView]);
  openAdminForm(choices[idx>=0?idx:0]);
});

window.addEventListener('popstate',(e)=>navigate(e.state?.view||'home',false));

/* INIT */
renderNav();
navigate(INITIAL_VIEW,false);
</script>
</body>
</html>
