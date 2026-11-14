<?php

declare(strict_types=1);

return [

    'page' => [
        'title' => 'Aparência',
    ],

    'mode' => [
        'heading' => 'Modo',
        'description' => 'Claro, escuro ou igual ao do teu sistema. Fica guardado neste navegador.',
    ],

    'palettes' => [
        'heading' => 'Paleta de cores',
        'description' => 'Muda a barra de navegação, os botões e o fundo. Fica guardada na tua conta e acompanha-te em qualquer dispositivo.',
        'label' => 'Paletas',
        'in_use' => 'Em uso',
    ],

    'custom' => [
        'heading' => 'As tuas paletas',
        'description' => 'Parte de uma paleta existente e muda as cores.',
        'empty' => 'Ainda não criaste nenhuma paleta.',
        'created_by_you' => 'Criada por ti',
    ],

    'fields' => [
        'name' => 'Nome',
        'base' => 'Começar a partir de',
        'primary' => [
            'label' => 'Cor principal',
            'helper' => 'Botão principal, ligações e gráficos.',
        ],
        'navigation' => [
            'label' => 'Barra de navegação',
            'helper' => 'O texto fica branco ou preto, conforme o que se ler melhor.',
        ],
        'background' => [
            'label' => 'Fundo',
            'helper' => 'Por trás dos cartões e das tabelas, no modo claro.',
        ],
    ],

    'actions' => [
        'create' => [
            'label' => 'Criar paleta',
            'modal_heading' => 'Criar paleta',
            'modal_description' => 'Três cores chegam. As cores de estado (sucesso, aviso, erro) são iguais em todas as paletas.',
            'submit' => 'Guardar e usar',
        ],
        'edit' => [
            'label' => 'Editar',
            'modal_heading' => 'Editar paleta «:name»',
            'submit' => 'Guardar paleta',
        ],
        'delete' => [
            'label' => 'Eliminar',
            'modal_heading' => 'Eliminar a paleta «:name»?',
            'modal_description' => 'Se for a que estás a usar, voltas à paleta predefinida.',
            'submit' => 'Eliminar paleta',
        ],
    ],

    'notifications' => [
        'deleted' => 'Paleta eliminada',
    ],

    'breakdown' => [
        'total' => 'total',
        'others' => 'Outras',
        'loading' => 'A carregar o gráfico',
        'empty' => 'Sem valores neste período.',
    ],

    'mobile' => [
        'shortcuts' => 'Atalhos',
        'menu' => 'Menu',
        'more' => 'Mais',
        'close' => 'Fechar',
        'search' => 'Pesquisar no menu',
        'search_placeholder' => 'Ir para…',
        'pages' => '{1} :count página|[2,*] :count páginas',
    ],

];
