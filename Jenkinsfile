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
    stage('Checkout Source') {
      steps { 
        git credentialsId: 'github-token', url: 'https://github.com/setegnabebe/lpms.git'
      }
    }
    stage('Build image') {
      steps{
        script {
           sh "docker build -t ${dockerimagename} ."
           dockerImage = docker.build dockerimagename
        }
      }
    }
    stage('Pushing Image') {
      environment {
          registryCredential = 'private_registry_login'
           }
      steps{
        script {
          docker.withRegistry( 'https://10.10.1.131:5000', registryCredential ) {
            dockerImage.push("latest")
          }
        }
      }
    }
    stage('Deploying  to Kubernetes') {
      steps {
        script {
          kubernetesDeploy(configs: "deployment.yaml", 
                                         "service.yaml")
        }
      }
    }
  }
}
