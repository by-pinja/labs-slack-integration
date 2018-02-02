@Library("PTCSLibrary") _

podTemplate(label: 'labs-slack-integration-symfony',
  containers: [
    containerTemplate(
      name: 'php',
      image: 'php:7.1.14',
      ttyEnabled: true,
      command: '/bin/sh -c',
      args: 'cat'
    ),
    containerTemplate(
      name: 'docker',
      image: 'ptcos/docker-client:latest',
      alwaysPullImage: true,
      ttyEnabled: true,
      command: '/bin/sh -c',
      args: 'cat'
    ),
  ]
) {
  def project = "labs-slack-integration"
  def branch = (env.BRANCH_NAME)
  def namespace = "labs-slack-integration"
  def notifySlackChannel = "#jenkins"

  try {
    node('type-symfony') {
      stage('Checkout') {
        checkout_with_tags()
      }
      stage('Build') {
        container('php') {
          withEnv(["COMPOSER_ALLOW_SUPERUSER=1"]) {
            sh """
              apt-get update
              apt-get install -y zlib1g-dev git
              docker-php-ext-install zip pdo pdo_mysql bcmath
              cp build-config/php.ini /usr/local/etc/php/
              cp build-config/php-cli.ini /usr/local/etc/php/
              cp .env.jenkins .env
              cp .env.jenkins .env.test
              curl -sS https://getcomposer.org/installer | php
              php composer.phar install
            """
          }
        }
      }
      stage('Test') {
        container('php') {
          sh """
            ./bin/phpunit
          """
        }
      }
      stage('Package') {
        container('docker') {
          def published = publishContainerToGcr(project, branch);

          toK8sTestEnv() {
            sh """
              kubectl set image deployment/labs-slack-deployment $project=$published.image:$published.tag --namespace=$namespace
            """
          }
        }
      }
    }
  } catch (e) {
    currentBuild.result = "FAILED"
    throw e
  }
}
