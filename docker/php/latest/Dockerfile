FROM nanoninja/php-fpm:latest
RUN useradd -u 1000 docker && echo docker:docker | chpasswd 
RUN apt-get update 
RUN apt-get install -y openssh-server 
RUN ssh-keygen -A
RUN echo root:parking | /usr/sbin/chpasswd
CMD /etc/init.d/ssh start; php-fpm
