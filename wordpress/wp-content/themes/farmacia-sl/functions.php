<?php
defined('ABSPATH') || exit;

/* ------------------------------------------------------------------ */
/* CUSTOM POST TYPES                                                    */
/* ------------------------------------------------------------------ */
add_action('init', function () {

    // Comunicados
    register_post_type('comunicado', [
        'labels'       => ['name' => 'Comunicados', 'singular_name' => 'Comunicado', 'add_new_item' => 'Novo Comunicado'],
        'public'       => true,
        'show_in_rest' => true,
        'rest_base'    => 'comunicados',
        'supports'     => ['title', 'editor', 'author', 'thumbnail'],
        'menu_icon'    => 'dashicons-megaphone',
        'has_archive'  => false,
    ]);

    // POPs e Protocolos
    register_post_type('pop_protocolo', [
        'labels'       => ['name' => 'POPs e Protocolos', 'singular_name' => 'POP/Protocolo', 'add_new_item' => 'Novo POP'],
        'public'       => true,
        'show_in_rest' => true,
        'rest_base'    => 'pops',
        'supports'     => ['title', 'editor', 'author'],
        'menu_icon'    => 'dashicons-book-alt',
        'has_archive'  => false,
    ]);

    // Treinamentos
    register_post_type('treinamento', [
        'labels'       => ['name' => 'Treinamentos', 'singular_name' => 'Treinamento', 'add_new_item' => 'Novo Treinamento'],
        'public'       => true,
        'show_in_rest' => true,
        'rest_base'    => 'treinamentos',
        'supports'     => ['title', 'editor', 'author'],
        'menu_icon'    => 'dashicons-welcome-learn-more',
        'has_archive'  => false,
    ]);

    // Medicamentos Padronizados
    register_post_type('med_padronizado', [
        'labels'       => ['name' => 'Padronização', 'singular_name' => 'Medicamento', 'add_new_item' => 'Novo Medicamento'],
        'public'       => true,
        'show_in_rest' => true,
        'rest_base'    => 'medicamentos',
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-plus-alt',
        'has_archive'  => false,
    ]);

    // LASA
    register_post_type('lasa_par', [
        'labels'       => ['name' => 'LASA', 'singular_name' => 'Par LASA', 'add_new_item' => 'Novo Par LASA'],
        'public'       => false,
        'show_ui'      => true,
        'show_in_rest' => true,
        'rest_base'    => 'lasa',
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-warning',
    ]);

    // Alta Vigilância
    register_post_type('alta_vigilancia', [
        'labels'       => ['name' => 'Alta Vigilância', 'singular_name' => 'Medicamento Alta Vig.', 'add_new_item' => 'Novo'],
        'public'       => false,
        'show_ui'      => true,
        'show_in_rest' => true,
        'rest_base'    => 'alta-vigilancia',
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-shield-alt',
    ]);

    // Fluxos e Processos
    register_post_type('fluxo_processo', [
        'labels'       => ['name' => 'Fluxos', 'singular_name' => 'Fluxo', 'add_new_item' => 'Novo Fluxo'],
        'public'       => false,
        'show_ui'      => true,
        'show_in_rest' => true,
        'rest_base'    => 'fluxos',
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-randomize',
    ]);

    // Mensagens de Contato (anônimas)
    register_post_type('contato_msg', [
        'labels'       => ['name' => 'Mensagens', 'singular_name' => 'Mensagem'],
        'public'       => false,
        'show_ui'      => true,
        'supports'     => ['title', 'editor'],
        'menu_icon'    => 'dashicons-email-alt',
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true,
    ]);
});

/* ------------------------------------------------------------------ */
/* TAXONOMIAS                                                           */
/* ------------------------------------------------------------------ */
add_action('init', function () {
    register_taxonomy('cat_comunicado', 'comunicado', [
        'labels'       => ['name' => 'Categorias', 'singular_name' => 'Categoria'],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'cat-comunicado'],
    ]);
    register_taxonomy('cat_pop', 'pop_protocolo', [
        'labels'       => ['name' => 'Categorias POP', 'singular_name' => 'Categoria POP'],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'cat-pop'],
    ]);
});

/* ------------------------------------------------------------------ */
/* META FIELDS                                                          */
/* ------------------------------------------------------------------ */
add_action('init', function () {
    // Comunicado
    foreach (['fsl_resumo', 'fsl_anexo_url', 'fsl_tipo'] as $key) {
        register_post_meta('comunicado', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
    // POP
    foreach (['fsl_codigo', 'fsl_versao', 'fsl_categoria', 'fsl_arquivo_url'] as $key) {
        register_post_meta('pop_protocolo', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
    // Treinamento
    foreach (['fsl_video_url', 'fsl_pdf_url', 'fsl_quiz_url', 'fsl_duracao', 'fsl_publico', 'fsl_descricao'] as $key) {
        register_post_meta('treinamento', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
    // Medicamento
    foreach (['fsl_classe', 'fsl_apresentacao', 'fsl_status_med'] as $key) {
        register_post_meta('med_padronizado', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
    // LASA
    foreach (['fsl_med_a', 'fsl_med_b', 'fsl_nota_lasa'] as $key) {
        register_post_meta('lasa_par', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
    // Fluxo
    register_post_meta('fluxo_processo', 'fsl_passos', [
        'show_in_rest' => ['schema' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['t' => ['type' => 'string'], 'd' => ['type' => 'string']]]]],
        'single'       => true,
        'type'         => 'array',
        'auth_callback' => fn() => current_user_can('edit_posts'),
    ]);
    // Contato
    foreach (['fsl_setor', 'fsl_tipo_contato', 'fsl_anonimo'] as $key) {
        register_post_meta('contato_msg', $key, ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => fn() => current_user_can('edit_posts')]);
    }
});

/* ------------------------------------------------------------------ */
/* REST API — salvar/excluir posts (admin)                             */
/* ------------------------------------------------------------------ */
add_action('rest_api_init', function () {

    $meta_map = [
        'comunicado'     => ['tipo' => 'fsl_tipo', 'resumo' => 'fsl_resumo', 'anexo' => 'fsl_anexo_url'],
        'pop'            => ['codigo' => 'fsl_codigo', 'versao' => 'fsl_versao', 'categoria' => 'fsl_categoria', 'arquivo' => 'fsl_arquivo_url'],
        'treinamento'    => ['descricao' => 'fsl_descricao', 'publico' => 'fsl_publico', 'duracao' => 'fsl_duracao', 'video' => 'fsl_video_url', 'pdf' => 'fsl_pdf_url', 'quiz' => 'fsl_quiz_url'],
        'medicamento'    => ['classe' => 'fsl_classe', 'apresentacao' => 'fsl_apresentacao', 'status' => 'fsl_status_med'],
        'lasa'           => ['med_a' => 'fsl_med_a', 'med_b' => 'fsl_med_b', 'nota' => 'fsl_nota_lasa'],
        'altaVigilancia' => [],
        'fluxo'          => ['passos' => 'fsl_passos'],
    ];

    $type_map = [
        'comunicado'     => 'comunicado',
        'pop'            => 'pop_protocolo',
        'treinamento'    => 'treinamento',
        'medicamento'    => 'med_padronizado',
        'lasa'           => 'lasa_par',
        'altaVigilancia' => 'alta_vigilancia',
        'fluxo'          => 'fluxo_processo',
    ];

    register_rest_route('fsl/v1', '/save', [
        'methods'             => 'POST',
        'callback'            => function (WP_REST_Request $req) use ($type_map, $meta_map) {
            $type      = sanitize_key($req->get_param('type') ?: '');
            $id        = (int) ($req->get_param('id') ?: 0);
            $fields    = (array) ($req->get_param('fields') ?: []);
            $post_type = $type_map[$type] ?? null;

            if (!$post_type) return new WP_Error('invalid_type', 'Tipo inválido', ['status' => 400]);
            $title = sanitize_text_field($fields['titulo'] ?? '');
            if (empty($title)) return new WP_Error('missing_title', 'Título obrigatório', ['status' => 400]);

            $post_data = [
                'post_type'    => $post_type,
                'post_title'   => $title,
                'post_content' => wp_kses_post($fields['corpo'] ?? ''),
                'post_status'  => 'publish',
            ];
            if ($id > 0) {
                $post_data['ID'] = $id;
                $post_id = wp_update_post($post_data, true);
            } else {
                $post_id = wp_insert_post($post_data, true);
            }
            if (is_wp_error($post_id)) return new WP_Error('save_error', $post_id->get_error_message(), ['status' => 500]);

            foreach ($meta_map[$type] ?? [] as $field_key => $meta_key) {
                if (!array_key_exists($field_key, $fields)) continue;
                $val = $fields[$field_key];
                if ($field_key === 'passos') {
                    if (is_string($val)) $val = json_decode($val, true) ?: [];
                    update_post_meta($post_id, $meta_key, wp_json_encode($val));
                } else {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($val));
                }
            }
            return ['ok' => true, 'id' => $post_id];
        },
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ]);

    register_rest_route('fsl/v1', '/delete', [
        'methods'             => 'POST',
        'callback'            => function (WP_REST_Request $req) {
            $id = (int) ($req->get_param('id') ?: 0);
            if (!$id) return new WP_Error('missing_id', 'ID obrigatório', ['status' => 400]);
            $result = wp_delete_post($id, true);
            if (!$result) return new WP_Error('delete_error', 'Erro ao excluir', ['status' => 500]);
            return ['ok' => true, 'id' => $id];
        },
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ]);
});

/* ------------------------------------------------------------------ */
/* REST API — endpoint público para criação anônima de mensagem        */
/* ------------------------------------------------------------------ */
add_action('rest_api_init', function () {
    register_rest_route('fsl/v1', '/contato', [
        'methods'             => 'POST',
        'callback'            => function (WP_REST_Request $req) {
            $nome  = sanitize_text_field($req->get_param('nome') ?: 'Anônimo');
            $setor = sanitize_text_field($req->get_param('setor') ?: '');
            $tipo  = sanitize_text_field($req->get_param('tipo') ?: '');
            $msg   = sanitize_textarea_field($req->get_param('mensagem') ?: '');
            if (empty($msg)) return new WP_Error('empty', 'Mensagem obrigatória', ['status' => 400]);
            $id = wp_insert_post([
                'post_type'    => 'contato_msg',
                'post_title'   => $nome . ' — ' . $tipo,
                'post_content' => $msg,
                'post_status'  => 'private',
                'meta_input'   => ['fsl_setor' => $setor, 'fsl_tipo_contato' => $tipo, 'fsl_anonimo' => 'yes'],
            ]);
            if (is_wp_error($id)) return new WP_Error('fail', 'Erro ao salvar', ['status' => 500]);
            return ['ok' => true, 'id' => $id];
        },
        'permission_callback' => '__return_true',
    ]);

    // Endpoint de busca global
    register_rest_route('fsl/v1', '/busca', [
        'methods'             => 'GET',
        'callback'            => function (WP_REST_Request $req) {
            $q = sanitize_text_field($req->get_param('q') ?: '');
            if (strlen($q) < 2) return [];
            $types = ['comunicado', 'pop_protocolo', 'treinamento', 'med_padronizado'];
            $results = [];
            foreach ($types as $type) {
                $posts = get_posts(['post_type' => $type, 's' => $q, 'numberposts' => 5]);
                foreach ($posts as $p) {
                    $results[] = ['type' => $type, 'id' => $p->ID, 'title' => $p->post_title];
                }
            }
            return $results;
        },
        'permission_callback' => '__return_true',
    ]);
});

/* ------------------------------------------------------------------ */
/* PERMISSÕES — subscriber pode ler CPTs públicos                      */
/* ------------------------------------------------------------------ */
add_action('admin_init', function () {
    $subscriber = get_role('subscriber');
    if ($subscriber && !$subscriber->has_cap('read')) {
        $subscriber->add_cap('read');
    }
});

/* ------------------------------------------------------------------ */
/* REDIRECIONAR USUÁRIOS NÃO-ADMIN PARA O PORTAL                       */
/* ------------------------------------------------------------------ */
add_action('template_redirect', function () {
    if (is_admin()) return;
    $portal_pages = ['portal','comunicados','pops','treinamentos','padronizacao','seguranca','fluxos','contato'];
    foreach ($portal_pages as $slug) {
        if (is_page($slug)) return;
    }
    $portal = get_page_by_path('portal');
    if ($portal) {
        wp_redirect(get_permalink($portal));
        exit;
    }
});

/* ------------------------------------------------------------------ */
/* REGISTRAR PÁGINA DE PORTAL SE NÃO EXISTIR                           */
/* ------------------------------------------------------------------ */
add_action('after_switch_theme', function () {
    $sections = [
        'portal'       => 'Portal da Farmácia',
        'comunicados'  => 'Comunicados',
        'pops'         => 'POPs e Protocolos',
        'treinamentos' => 'Treinamentos',
        'padronizacao' => 'Padronização',
        'seguranca'    => 'Segurança do Paciente',
        'fluxos'       => 'Fluxos e Processos',
        'contato'      => 'Contato',
    ];
    foreach ($sections as $slug => $title) {
        if (!get_page_by_path($slug)) {
            $id = wp_insert_post([
                'post_title'    => $title,
                'post_name'     => $slug,
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'page_template' => 'page-portal.php',
            ]);
            if ($slug === 'portal') {
                update_option('fsl_portal_page_id', $id);
                update_option('page_on_front', $id);
                update_option('show_on_front', 'page');
            }
        }
    }
});

/* ------------------------------------------------------------------ */
/* SUPORTE AO TEMA                                                      */
/* ------------------------------------------------------------------ */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['script', 'style']);
});
