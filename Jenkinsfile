pipeline {
  environment {
    dockerimagename = "10.10.1.131:5000/lpms-app"
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
          docker.withRegistry( 'https://10.10.1.131:5000/', registryCredential ) {
            dockerImage.push("latest")
          }
        }
      }
    }
    stage('Deploying  to Kubernetes') {
      steps {
        script {
          kubernetesDeploy(configs: "LPMS-main/deploy/deployment.yaml", 
                                         "LPMS-main/deploy/service.yaml")
        }
      }
    }
  }
}
