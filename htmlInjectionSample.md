# HTML Injection Sample

This is a sample HTML/CSS injection for the Custom Page embedding.

**Note:** React's `dangerouslySetInnerHTML` strips `<script>` tags, so JavaScript won't execute. Use CSS animations only.

## Copy the code below into an Embedding item:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .fullscreen-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            overflow: hidden;
        }

        #particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
            animation: float 5s ease-out infinite;
            bottom: 0;
        }

        .particle:nth-child(1) { animation-duration: 4s; animation-delay: 0s; left: 10%; }
        .particle:nth-child(2) { animation-duration: 5.5s; animation-delay: 0.5s; left: 80%; }
        .particle:nth-child(3) { animation-duration: 6s; animation-delay: 1s; left: 30%; }
        .particle:nth-child(4) { animation-duration: 4.5s; animation-delay: 1.5s; left: 70%; }
        .particle:nth-child(5) { animation-duration: 5s; animation-delay: 2s; left: 50%; }
        .particle:nth-child(6) { animation-duration: 6.5s; animation-delay: 0.3s; left: 20%; }
        .particle:nth-child(7) { animation-duration: 5.2s; animation-delay: 1.2s; left: 90%; }
        .particle:nth-child(8) { animation-duration: 4.8s; animation-delay: 0.8s; left: 40%; }
        .particle:nth-child(9) { animation-duration: 5.8s; animation-delay: 1.8s; left: 60%; }
        .particle:nth-child(10) { animation-duration: 5.3s; animation-delay: 0.6s; left: 15%; }

        .container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 60px 80px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 90vw;
            z-index: 2;
            animation: glow 8s ease-in-out infinite;
        }

        h1 {
            font-size: 4rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: none;
            animation: fadeInDown 1s ease-out;
        }

        p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .highlight {
            color: #764ba2;
            font-weight: bold;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100vh) translateX(20px) scale(0.3);
                opacity: 0;
            }
        }

        @keyframes glow {
            0% { box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4); }
            25% { box-shadow: 0 20px 60px rgba(234, 102, 126, 0.4); }
            50% { box-shadow: 0 20px 60px rgba(126, 234, 102, 0.4); }
            75% { box-shadow: 0 20px 60px rgba(234, 126, 234, 0.4); }
            100% { box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4); }
        }
    </style>
</head>
<body>
    <div class="fullscreen-wrapper">
        <div id="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        <div class="container">
            <h1>Custom Page</h1>
            <p>This is a <span class="highlight">custom HTML injection</span> with CSS animations.</p>
            <p>It renders directly in the React component using dangerouslySetInnerHTML.</p>
        </div>
    </div>
</body>
</html>
```
