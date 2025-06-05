pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                git branch:'infraestrutura', url:'https://github.com/Nicolasortiiz/VagaXpress.git'
            }
        }
        stage('Build') {
            steps {
                dir('app') {
                    script {
                        def dockerfiles_app = [
                            [nome: 'imagem-front', arq: 'front/Dockerfile'],
                            [nome: 'imagem-gateway', arq: 'gateway/Dockerfile'],
                            [nome: 'imagem-gestao-veiculos', arq: 'gestao_veiculos/Dockerfile'],
                            [nome: 'imagem-notificacoes', arq: 'notificacoes/Dockerfile'],
                            [nome: 'imagem-pagamento', arq: 'pagamento/Dockerfile'],
                            [nome: 'imagem-vagas', arq: 'vagas/Dockerfile'],
                        ]

                        for (int i = 0; i < dockerfiles_app.size(); i++) {
                            def dockerfile = dockerfiles_app[i]
                            sh "docker build -t ${dockerfile.nome} -f ${dockerfile.arq} ."
                        }
                    }
                }
                dir('bd') {
                    script {
                        def dockerfiles_bd = [
                            [nome: 'imagem-db-estacionamento', arq: 'estacionamento/Dockerfile.db'],
                            [nome: 'imagem-db-notificacao', arq: 'notificacao/Dockerfile.db'],
                            [nome: 'imagem-db-pagamento', arq: 'pagamento/Dockerfile.db'],
                            [nome: 'imagem-db-usuario', arq: 'usuario/Dockerfile.db'],
                        ]

                        for (int i = 0; i < dockerfiles_bd.size(); i++) {
                            def dockerfile = dockerfiles_bd[i]
                            sh "docker build -t ${dockerfile.nome} -f ${dockerfile.arq} ."
                        }
                    }
                }
                dir('sensor') {
                    script {
                        sh 'docker build -t imagem-sensor -f Dockerfile .'
                    }
                }
            }
        }
        stage('Deploy') {
            steps {
                dir('kubernetes') {
                    script {
                        sh 'kubectl apply -f db-deployment.yml'
                        sh 'kubectl apply -f db-service.yml'

                        sh 'kubectl apply -f redis-deployment.yml'
                        sh 'kubectl apply -f redis-service.yml'

                        sh 'kubectl apply -f servicos-deployment.yml'
                        sh 'kubectl apply -f servicos-services.yml'

                        sh 'kubectl apply -f gateway-deployment.yml'
                        sh 'kubectl apply -f gateway-service.yml'

                        sh 'kubectl apply -f sensor-deployment.yml'
                        sh 'kubectl apply -f sensor-service.yml'

                        sh 'kubectl apply -f front-deployment.yml'
                        sh 'kubectl apply -f front-service.yml'
                    }
                }
            }
        }
    }

    post {
        success {
            echo 'Pipeline executada com sucesso!'
        }
        failure {
            echo 'Pipeline falhou na execução!'
        }
    }
}
