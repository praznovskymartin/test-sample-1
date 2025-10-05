<?php
header('Content-Type: text/html; charset=utf-8');

// Get the GIF URL from query parameter
$gifUrl = $_GET['gif'] ?? '';

if (empty($gifUrl)) {
    die('Error: No GIF URL provided');
}

// Validate URL (basic security check)
if (!filter_var($gifUrl, FILTER_VALIDATE_URL)) {
    die('Error: Invalid URL provided');
}

// Get GIF dimensions
$imageInfo = @getimagesize($gifUrl);
if ($imageInfo === false) {
    die('Error: Unable to read image information');
}

$originalWidth = $imageInfo[0];
$originalHeight = $imageInfo[1];
$aspectRatio = $originalHeight / $originalWidth;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gif-container {
            width: 100%;
            height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gif-image {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="gif-container">
        <img src="<?php echo htmlspecialchars($gifUrl); ?>" 
             class="gif-image" 
             alt="GIF Image"
             data-original-width="<?php echo $originalWidth; ?>"
             data-original-height="<?php echo $originalHeight; ?>"
             data-aspect-ratio="<?php echo $aspectRatio; ?>">
    </div>
    
    <script>
        function resizeGif() {
            const img = document.querySelector('.gif-image');
            const container = document.querySelector('.gif-container');
            
            if (!img || !container) return;
            
            const parentWidth = window.innerWidth;
            const aspectRatio = parseFloat(img.dataset.aspectRatio);
            
            // Calculate new dimensions
            const newWidth = Math.min(parentWidth, parseInt(img.dataset.originalWidth));
            const newHeight = newWidth * aspectRatio;
            
            // Apply dimensions
            img.style.width = newWidth + 'px';
            img.style.height = newHeight + 'px';
            
            // Resize iframe height to match content
            if (window.parent && window.parent !== window) {
                try {
                    window.parent.postMessage({
                        type: 'resize',
                        width: newWidth,
                        height: newHeight
                    }, '*');
                } catch (e) {
                    console.log('Cannot communicate with parent frame');
                }
            }
        }
        
        // Resize on load and window resize
        window.addEventListener('load', resizeGif);
        window.addEventListener('resize', resizeGif);
        
        // Resize when image loads
        document.querySelector('.gif-image').addEventListener('load', resizeGif);
    </script>
</body>
</html>
