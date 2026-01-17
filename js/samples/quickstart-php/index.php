<?php
// Copyright (c) Microsoft Corporation. All rights reserved.
// Licensed under the MIT License.

// Load environment variables
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Handle AJAX request for token and subdomain
if (isset($_GET['action']) && $_GET['action'] === 'getTokenAndSubdomain') {
    header('Content-Type: application/json');
    
    try {
        $tenantId = getenv('TENANT_ID');
        $clientId = getenv('CLIENT_ID');
        $clientSecret = getenv('CLIENT_SECRET');
        $subdomain = getenv('SUBDOMAIN');
        
        if (empty($tenantId) || empty($clientId) || empty($clientSecret) || empty($subdomain)) {
            throw new Exception('Missing required environment variables. Please check your .env file.');
        }
        
        // Prepare the token request
        $tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/token";
        
        $postData = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'resource' => 'https://cognitiveservices.azure.com/'
        ];
        
        // Make the request using cURL
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('cURL error: ' . $error);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to acquire Azure AD token. HTTP Status: ' . $httpCode);
        }
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['access_token'])) {
            throw new Exception('AAD Authentication error: No access token in response');
        }
        
        $token = $responseData['access_token'];
        
        echo json_encode([
            'token' => $token,
            'subdomain' => $subdomain
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Unable to acquire Azure AD token. Check the debugger for more information.',
            'message' => $e->getMessage()
        ]);
    }
    
    exit;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Immersive Reader PHP Quickstart</title>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script type='text/javascript' src='https://ircdname.azureedge.net/immersivereadersdk/immersive-reader-sdk.1.4.0.js'></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous"/>
        <style type="text/css">
            .immersive-reader-button {
                background-color: white;
                margin-top: 5px;
                border: 1px solid black;
                float: right;
            }

            nav {
                margin-top: 10px;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <button class="immersive-reader-button" data-button-style="iconAndText" data-locale="en">Immersive Reader</button>

            <h1 id="ir-title">About Immersive Reader</h1>
            <div id="ir-content" lang="en-us">
                <p>
                    Immersive Reader is a tool that implements proven techniques to improve reading comprehension for emerging readers, language learners, and people with learning differences.
                    The Immersive Reader is designed to make reading more accessible for everyone. The Immersive Reader
                    <ul>
                        <li>
                            Shows content in a minimal reading view
                        </li>
                        <li>
                            Displays pictures of commonly used words
                        </li>
                        <li>
                            Highlights nouns, verbs, adjectives, and adverbs
                        </li>
                        <li>
                            Reads your content out loud to you
                        </li>
                        <li>
                            Translates your content into another language
                        </li>
                        <li>
                            Breaks down words into syllables
                        </li>
                    </ul>
                </p>
                <h3>
                    The Immersive Reader is available in many languages.
                </h3>
                <p lang="es-es">
                    El Lector inmersivo está disponible en varios idiomas.
                </p>
                <p lang="zh-cn">
                    沉浸式阅读器支持许多语言
                </p>
                <p lang="de-de">
                    Der plastische Reader ist in vielen Sprachen verfügbar.
                </p>
                <p lang="ar-eg" dir="rtl" style="text-align:right">
                    يتوفر "القارئ الشامل" في العديد من اللغات.
                </p>
            </div>
        </div>

        <script type="text/javascript">
            function getTokenAndSubdomainAsync() {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: "?action=getTokenAndSubdomain",
                        type: "GET",
                        success: function (data) {
                            if (data.error) {
                                reject(data.error);
                            } else {
                                resolve(data);
                            }
                        },
                        error: function (err) {
                            reject(err);
                        }
                    });
                });
            }

            $(".immersive-reader-button").click(function () {
                handleLaunchImmersiveReader();
            });

            function handleLaunchImmersiveReader() {
                getTokenAndSubdomainAsync()
                    .then(function (response) {
                        const token = response["token"];
                        const subdomain = response["subdomain"];

                        // Learn more about chunk usage and supported MIME types https://docs.microsoft.com/azure/cognitive-services/immersive-reader/reference#chunk
                        const data = {
                            title: $("#ir-title").text(),
                            chunks: [{
                                content: $("#ir-content").html(),
                                mimeType: "text/html"
                            }]
                        };

                        // Learn more about options https://docs.microsoft.com/azure/cognitive-services/immersive-reader/reference#options
                        const options = {
                            "onExit": exitCallback,
                            "uiZIndex": 2000
                        };

                        ImmersiveReader.launchAsync(token, subdomain, data, options)
                            .catch(function (error) {
                                console.log(error);
                                alert("Error in launching the Immersive Reader. Check the console.");
                            });
                    })
                    .catch(function (error) {
                        console.log(error);
                        alert("Error in getting the Immersive Reader token and subdomain. Check the console.");
                    });
            }

            function exitCallback() {
                console.log("This is the callback function. It is executed when the Immersive Reader closes.");
            }
        </script>
    </body>
</html>
