<!DOCTYPE html>
<html lang="en-us">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>PPConnect</title>
  </head>

  <body>      
  <main>
        <div class="btn start-video" title='Ligar'>Ligar Câmera</div>
        <div class="btn take-picture" title='Tirar uma foto'>Obter Imagem</div>
        <div class="btn save-picture" title='Finalizar'>Salvar</div>
        <p>
        <video id="preview" width="320" height="240" muted autoplay> </video>
        <canvas id="imagem"></canvas> 
  </main>
    <script>
        function startCamera () {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: true })
                .then((stream) => {
                    document.getElementById('preview').srcObject = stream
                })
        }
        
        function stopCamera () {
            document.getElementById('preview')
                .srcObject
                .getVideoTracks()
                .forEach(track => track.stop())
        }
    
        document.querySelector('.start-video').addEventListener('click', event => {
            startCamera()
            document.getElementById('imagem').style.display = 'none'
            document.getElementById('preview').style.display = 'block'
        })
        
        var urlimagem=null;
        var dataURL = null;
            
        document.querySelector('.take-picture').addEventListener('click', event => {
            const canvas = document.getElementById('imagem')
            const context = canvas.getContext('2d')
            const video = document.getElementById('preview')
            canvas.width = video.offsetWidth
            canvas.height = video.offsetHeight
            context.drawImage(video, 0, 0, canvas.width, canvas.height)    
            video.style.display = 'none'
            canvas.style.display = 'block'
            
            canvas.toBlob(function(blob){
                urlimagem = URL.createObjectURL(blob)
            }, 'image/jpeg', 0.95)
            stopCamera()
            dataURL = canvas.toDataURL();
        })
        
        document.querySelector('.save-picture').addEventListener('click', event => { 
            $.ajax({ 
                type: 'POST', 
                url: 'http://localhost/ppconnectpolo/app/resources/teste.php', 
                data: {
                    'class': 'QuestoesDasProvasGeradasService',
                    'method': 'storeimagem',
                    'img' : dataURL
                }, 
                //dataType: 'json',
                success: function (response) { 
                    console.log(response);
                    $(".ui-dialog-titlebar-close").click();
                },
                    error: function(response) {
                    console.log(response);
                }
            });
            
        })
    </script>

</body>
</html>