pipeline {
  environment {
    baseImage = "lpms"
    dockerRegistry = "10.10.1.131:5000" 
    registryCredential = 'private_registry_login'
    dockerimagename = "${dockerRegistry}/${baseImage}:${BUILD_NUMBER}"
    dockerImage = ""
  }
  agent {
    kubernetes {
      label 'jenkins-agent'
      defaultContainer 'jnlp'
      yaml """
apiVersion: v1
kind: Pod
spec:
  containers:
  - name: docker
    image: docker:latest
    command:
    - cat
    tty: true
    volumeMounts:
    - name: docker-sock
      mountPath: /var/run/docker.sock
  volumes:
  - name: docker-sock
    hostPath:
      path: /var/run/docker.sock
"""
    }
  }
  stages {
    stage('Checkout Source') {
      steps { 
        git credentialsId: 'github-token', url: 'https://github.com/setegnabebe/lpms.git'
      }
    }
    stage('Build image') {
      steps{
        container('docker') {
          script {
            sh "docker build -t ${dockerimagename} ."
            dockerImage = docker.build dockerimagename
          }
        }
      }
    }
    stage('Pushing Image') {
      environment {
        registryCredential = 'private_registry_login'
      }
      steps{
        container('docker') {
          script {
            docker.withRegistry( 'https://10.10.1.131:5000', registryCredential ) {
              dockerImage.push("latest")
            }
          }
        }
      }
    }
    stage('Deploying to Kubernetes') {
      steps {
        script {
          kubernetesDeploy(configs: "deployment.yaml,service.yaml")
        }
      }
    }
  }
}
