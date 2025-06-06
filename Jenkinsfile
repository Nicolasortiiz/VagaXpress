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
                            [nome: 'imagem-front', arq: 'front'],
                            [nome: 'imagem-gateway', arq: 'gateway'],
                            [nome: 'imagem-gestao-veiculos', arq: 'gestao_veiculos'],
                            [nome: 'imagem-notificacoes', arq: 'notificacoes'],
                            [nome: 'imagem-pagamento', arq: 'pagamento'],
                            [nome: 'imagem-vagas', arq: 'vagas'],
                        ]

                        for (int i = 0; i < dockerfiles_app.size(); i++) {
                            def dockerfile = dockerfiles_app[i]
                            sh "docker build -t ${dockerfile.nome} -f '${dockerfile.arq}/Dockerfile' ${dockerfile.arq}"
                            sh "docker tag ${dockerfile.nome} localhost/${dockerfile.nome}"
                            sh "docker push localhost/${dockerfile.nome}"
                            sh "docker rmi -f localhost/${dockerfile.nome}"
                            sh "docker pull localhost/${dockerfile.nome}"
                        }
                    }
                }
                dir('bd') {
                    script {
                        def dockerfiles_bd = [
                            [nome: 'imagem-db-estacionamento', arq: 'estacionamento'],
                            [nome: 'imagem-db-notificacao', arq: 'notificacao'],
                            [nome: 'imagem-db-pagamento', arq: 'pagamento'],
                            [nome: 'imagem-db-usuario', arq: 'usuario'],
                        ]

                        for (int i = 0; i < dockerfiles_bd.size(); i++) {
                            def dockerfile = dockerfiles_bd[i]
                            sh "docker build -t ${dockerfile.nome} -f '${dockerfile.arq}/Dockerfile.db' ${dockerfile.arq}"
                            sh "docker tag ${dockerfile.nome} localhost/${dockerfile.nome}"
                            sh "docker push localhost/${dockerfile.nome}"
                            sh "docker rmi -f localhost/${dockerfile.nome}"
                            sh "docker pull localhost/${dockerfile.nome}"
                        }
                    }
                }
                dir('sensor') {
                    script {
                        sh 'docker build -t imagem-sensor -f Dockerfile .'
                        sh "docker tag imagem-sensor localhost/imagem-sensor"
                        sh "docker push localhost/imagem-sensor"
                        sh "docker rmi -f localhost/imagem-sensor"
                        sh "docker pull localhost/imagem-sensor"
                    }
                }
            }
        }
        stage('Deploy') {
            steps {
                dir('kubernetes') {
                    script {
                        sh 'microk8s kubectl apply -f db-deployment.yml'
                        sh 'microk8s kubectl apply -f db-service.yml'

                        sh 'microk8s kubectl apply -f redis-deployment.yml'
                        sh 'microk8s kubectl apply -f redis-service.yml'

                        sh 'microk8s kubectl apply -f servicos-deployment.yml'
                        sh 'microk8s kubectl apply -f servicos-services.yml'

                        sh 'microk8s kubectl apply -f gateway-deployment.yml'
                        sh 'microk8s kubectl apply -f gateway-service.yml'

                        sh 'microk8s kubectl apply -f sensor-deployment.yml'
                        sh 'microk8s kubectl apply -f sensor-service.yml'

                        sh 'microk8s kubectl apply -f front-deployment.yml'
                        sh 'microk8s kubectl apply -f front-service.yml'
                        sh 'microk8s kubectl apply -f front-ingress.yml'
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
