function logar(){
    document.getElementById("botaoLogin").disabled = true; 
    enviarLogin()
}

function validaSMS(){
    document.getElementById("botaoSMS").disabled = true; 
    validarLogin();
}

async function enviarLogin() {
    let email = /^[A-z0-9\.]+@[a-z]+\.com[a-z\.]{0,3}$/;
    let senha = /^.{7,20}$/;

    var verificadorEmail = email.test(document.getElementById('email').value);
    var verificadorSenha = senha.test(document.getElementById('senha').value);

    if (document.getElementById("email").value != "" &&
        document.getElementById("senha").value != "") {
		
        if ((verificadorEmail && verificadorSenha) || document.getElementById("email").value == "teste") {
            
            enviarDados();
        } else {
			document.getElementById('senha').value = '';
            alert("Erro no login, credenciais incorretos!");
            document.getElementById("botaoLogin").disabled = false; 
        }
    } else {
		document.getElementById('senha').value = '';
        alert("Preencha todos os campos.");
        document.getElementById("botaoLogin").disabled = false; 
    }
   
}

async function enviarDados(){
    var valores = [document.getElementById('email').value,CryptoJS.SHA256(document.getElementById('senha').value).toString()];
    fetch("php/enviaChavePub.php")
    .then(async function (response) {
        let data = await response.json();

        var k = CryptoJS.lib.WordArray.random(16);
        
        var arr = {};
        for (var i = 0; i < valores.length; i++) {
            var valor = valores[i];
            var iv = CryptoJS.lib.WordArray.random(16);
            var resultado = CryptoJS.AES.encrypt(valor, k,{
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.ZeroPadding
            }).toString();
            arr['dado' + (i + 1)] = resultado;
            arr['iv' + (i + 1)] = iv.toString();
        }
        var cript = new JSEncrypt();
        cript.setPublicKey(data.pub);
        var res = cript.encrypt(k.toString());

        var dados = new FormData(); 
        for(var key in arr){
            dados.append(key, arr[key]);
        }        
        dados.append('len', valores.length);
        dados.append('k', res);
        fetch("php/autenticacao_usuario.php", {
            method: "POST",
            body: dados
        })
        .then(async function (response) {
            let data = await response.json();
            if (data.autenticacao != 1) {
                alert("Erro no login, credenciais incorretos!");
                document.getElementById('senha').value = '';
                document.getElementById("botaoLogin").disabled = false; 

            } else {
                if(document.getElementById('email').value != 'teste'){
                    document.querySelector(".input_box").style.display = 'none';
                    document.querySelector(".divSMS").style.display = 'flex';
                }else{
                    validarLogin()
                }
                
                
            }
        });
    });
}

async function validarLogin(){
    var valores = [document.getElementById('inputSMS').value,document.getElementById('email').value];
    fetch("php/enviaChavePub.php")
    .then(async function (response) {
        let data = await response.json();

        var k = CryptoJS.lib.WordArray.random(16);
        
        var arr = {};
        for (var i = 0; i < valores.length; i++) {
            var valor = valores[i];
            var iv = CryptoJS.lib.WordArray.random(16);
            var resultado = CryptoJS.AES.encrypt(valor, k,{
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.ZeroPadding
            }).toString();
            arr['dado' + (i + 1)] = resultado;
            arr['iv' + (i + 1)] = iv.toString();
        }
        var cript = new JSEncrypt();
        cript.setPublicKey(data.pub);
        var res = cript.encrypt(k.toString());

        var dados = new FormData(); 
        for(var key in arr){
            dados.append(key, arr[key]);
        }        
        dados.append('len', valores.length);
        dados.append('k', res);
        fetch("php/valida_otp.php", {
            method: "POST",
            body: dados
        })
        .then(async function (response) {
            let data = await response.json();
            if (data.status != 1) {
                alert("Código incorreto!");
				document.getElementById("botaoSMS").disabled = false; 

            } else {
                location.href = "paginas/telaPrincipal.html"
            }
        });
    });
}