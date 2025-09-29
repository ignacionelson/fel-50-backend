<?php
$collectionFile = __DIR__ . '/../postman_collection.json';

if (!file_exists($collectionFile)) {
    die('Postman collection not found');
}

$collectionData = json_decode(file_get_contents($collectionFile), true);

function generateId($text) {
    return preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $text)));
}

function extractEndpoints($items, &$endpoints = [], $prefix = '') {
    foreach ($items as $item) {
        if (isset($item['item'])) {
            $folderName = $prefix ? $prefix . ' > ' . $item['name'] : $item['name'];
            extractEndpoints($item['item'], $endpoints, $folderName);
        } else if (isset($item['request'])) {
            $method = $item['request']['method'] ?? 'GET';
            $url = '';

            if (isset($item['request']['url'])) {
                if (is_string($item['request']['url'])) {
                    $url = $item['request']['url'];
                } else if (isset($item['request']['url']['raw'])) {
                    $url = $item['request']['url']['raw'];
                }
            }

            $endpoints[] = [
                'name' => $item['name'],
                'folder' => $prefix,
                'method' => $method,
                'url' => $url,
                'id' => generateId($prefix . '-' . $item['name']),
                'data' => $item
            ];
        }
    }
    return $endpoints;
}

$endpoints = extractEndpoints($collectionData['item']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($collectionData['info']['name']); ?> - Documentación API</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #2d3748;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1a202c;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #718096;
            margin-bottom: 8px;
            padding-left: 12px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar a {
            display: block;
            padding: 8px 12px;
            color: #4a5568;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: #edf2f7;
            color: #1a202c;
        }

        .sidebar .method {
            display: inline-block;
            width: 50px;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            margin-right: 8px;
        }

        .method.get { background: #48bb78; color: white; }
        .method.post { background: #4299e1; color: white; }
        .method.put { background: #ed8936; color: white; }
        .method.delete { background: #f56565; color: white; }
        .method.patch { background: #9f7aea; color: white; }

        .content {
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 10px;
        }

        .header p {
            color: #718096;
            font-size: 16px;
        }

        .endpoint {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            scroll-margin-top: 20px;
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .endpoint-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            flex: 1;
        }

        .endpoint-method {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .endpoint-url {
            background: #f7fafc;
            padding: 12px 15px;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 14px;
            color: #2d3748;
            margin-bottom: 20px;
            word-break: break-all;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            margin: 25px 0 15px 0;
        }

        .parameters-table {
            width: 100%;
            border-collapse: collapse;
        }

        .parameters-table th {
            text-align: left;
            padding: 10px;
            background: #f7fafc;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
        }

        .parameters-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .code-block {
            background: #1a202c;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .response-example {
            margin-bottom: 20px;
        }

        .response-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .status-200 { background: #c6f6d5; color: #22543d; }
        .status-201 { background: #c6f6d5; color: #22543d; }
        .status-400 { background: #fed7d7; color: #742a2a; }
        .status-401 { background: #fed7d7; color: #742a2a; }
        .status-403 { background: #fed7d7; color: #742a2a; }
        .status-404 { background: #fed7d7; color: #742a2a; }
        .status-409 { background: #fed7d7; color: #742a2a; }
        .status-422 { background: #fed7d7; color: #742a2a; }
        .status-500 { background: #fed7d7; color: #742a2a; }

        .auth-badge {
            display: inline-block;
            background: #fef5e7;
            color: #7c4700;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .no-data {
            color: #718096;
            font-style: italic;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Endpoints de la API</h2>
            <?php
            $currentFolder = '';
            foreach ($endpoints as $endpoint):
                if ($currentFolder !== $endpoint['folder']):
                    if ($currentFolder !== ''): ?>
                        </ul>
                    </div>
                    <?php endif;
                    $currentFolder = $endpoint['folder'];
                    ?>
                    <div class="sidebar-section">
                        <div class="sidebar-section-title"><?php echo htmlspecialchars($currentFolder); ?></div>
                        <ul>
            <?php endif; ?>
                <li>
                    <a href="#<?php echo $endpoint['id']; ?>">
                        <span class="method <?php echo strtolower($endpoint['method']); ?>">
                            <?php echo $endpoint['method']; ?>
                        </span>
                        <?php echo htmlspecialchars($endpoint['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
            </div>
        </div>

        <div class="content">
            <div class="header">
                <h1><?php echo htmlspecialchars($collectionData['info']['name']); ?></h1>
                <p><?php echo htmlspecialchars($collectionData['info']['description'] ?? ''); ?></p>
            </div>

            <?php foreach ($endpoints as $endpoint): ?>
            <div class="endpoint" id="<?php echo $endpoint['id']; ?>">
                <div class="endpoint-header">
                    <h2 class="endpoint-title"><?php echo htmlspecialchars($endpoint['name']); ?></h2>
                    <span class="endpoint-method method <?php echo strtolower($endpoint['method']); ?>">
                        <?php echo $endpoint['method']; ?>
                    </span>
                    <?php if (isset($endpoint['data']['request']['auth'])): ?>
                    <span class="auth-badge">🔒 Requiere Autenticación</span>
                    <?php endif; ?>
                </div>

                <div class="endpoint-url">
                    <?php echo htmlspecialchars(str_replace('{{base_url}}', 'http://localhost:8000', $endpoint['url'])); ?>
                </div>

                <?php if (isset($endpoint['data']['request']['header']) && !empty($endpoint['data']['request']['header'])): ?>
                <div class="section-title">Cabeceras</div>
                <table class="parameters-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($endpoint['data']['request']['header'] as $header): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($header['key']); ?></td>
                            <td><?php echo htmlspecialchars($header['value']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (isset($endpoint['data']['request']['url']['variable']) && !empty($endpoint['data']['request']['url']['variable'])): ?>
                <div class="section-title">Parámetros de Ruta</div>
                <table class="parameters-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Valor de Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($endpoint['data']['request']['url']['variable'] as $param): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($param['key']); ?></td>
                            <td><?php echo htmlspecialchars($param['value'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (isset($endpoint['data']['request']['url']['query']) && !empty($endpoint['data']['request']['url']['query'])): ?>
                <div class="section-title">Parámetros de Consulta</div>
                <table class="parameters-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($endpoint['data']['request']['url']['query'] as $query): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($query['key']); ?></td>
                            <td><?php echo htmlspecialchars($query['value'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (isset($endpoint['data']['request']['body']['raw'])): ?>
                <div class="section-title">Cuerpo de la Solicitud</div>
                <div class="code-block">
                    <pre><?php echo htmlspecialchars(json_encode(json_decode($endpoint['data']['request']['body']['raw']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </div>
                <?php endif; ?>

                <?php if (isset($endpoint['data']['response']) && !empty($endpoint['data']['response'])): ?>
                <div class="section-title">Ejemplos de Respuesta</div>
                <?php foreach ($endpoint['data']['response'] as $response): ?>
                <div class="response-example">
                    <span class="response-status status-<?php echo $response['code'] ?? 200; ?>">
                        <?php echo $response['code'] ?? 200; ?> <?php echo $response['status'] ?? 'OK'; ?>
                    </span>
                    <h4 style="margin: 10px 0; color: #4a5568; font-size: 14px;">
                        <?php echo htmlspecialchars($response['name'] ?? 'Respuesta'); ?>
                    </h4>
                    <?php if (isset($response['body'])): ?>
                    <div class="code-block">
                        <pre><?php
                        $body = json_decode($response['body'], true);
                        if ($body) {
                            echo htmlspecialchars(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        } else {
                            echo htmlspecialchars($response['body']);
                        }
                        ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Desplazamiento suave para enlaces de ancla
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Resaltar sección activa en la barra lateral
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    document.querySelectorAll('.sidebar a').forEach(link => {
                        link.style.background = '';
                        link.style.color = '';
                    });
                    const activeLink = document.querySelector(`.sidebar a[href="#${id}"]`);
                    if (activeLink) {
                        activeLink.style.background = '#edf2f7';
                        activeLink.style.color = '#1a202c';
                    }
                }
            });
        }, {
            rootMargin: '-50% 0px -50% 0px'
        });

        document.querySelectorAll('.endpoint').forEach(endpoint => {
            observer.observe(endpoint);
        });
    </script>
</body>
</html>