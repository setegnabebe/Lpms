pipeline {
    environment {
        baseImage = "lpms"
        dockerRegistry = "10.10.1.131:5000" 
        registryCredential = 'private_registry_login'
        dockerimagename = "${dockerRegistry}/${baseImage}:${BUILD_NUMBER}"
        dockerImage = ""

    }

    agent any
    
    stages {

        stage('Checkout Latest Source') {
            steps {
                    git credentialsId: 'github-token', url: 'https://github.com/setegnabebe/lpms.git'
            }
        }

        stage('Build image') {
            steps {
                dir('LPMS-main') {
                    script {
                        sh "docker build -t ${dockerimagename} ."
                        sh "docker rmi -f ${baseImage}" // Remove previous image if needed
                        dockerImage = dockerimagename
                    }
                }
            }
        }

        stage('Pushing Image') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: registryCredential, usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {
                        sh "docker login -u ${USERNAME} -p ${PASSWORD} ${dockerRegistry}"
                        sh "docker push ${dockerimagename}"
                        sh "docker logout ${dockerRegistry}"
                    }
                }
            }
        }

        stage('Deploying App to Kubernetes') {
            steps {
                dir('/') {
                    script {
                        kubernetesDeploy(enableConfigSubstitution: true, configs: "deploy/deployment.yaml", kubeconfigId: "kubernetes")
                        kubernetesDeploy(configs: "deploy/service.yaml", kubeconfigId: "kubernetes")
                    }
                }
            }
        }

    }
}
