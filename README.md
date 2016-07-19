Docker
===================
1. [Install Docker](https://docs.docker.com/engine/installation/linux/ubuntulinux/#/install)
2. `sudo usermod -aG docker $USER`, re-login
3. [Install docker-compose](https://github.com/docker/compose/releases/latest)

Deploy app
===========
1. Clone repository
`git clone git@git.icc:consultnn/drivenn.git`
2. Move in project dir
`cd drivenn`
3. Run docker containers
`docker-compose up -d`
4. Set build scripts executable
`chmod +x ./build/*`
6. Init DEV environment
`./build/dev.sh`

Build scripts (placed in directory `./build`)
===========
 - prod.sh, dev.sh, test.sh - init environment