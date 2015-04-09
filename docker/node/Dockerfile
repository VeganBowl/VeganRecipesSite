FROM node:wheezy

RUN npm install --global bower gulp

RUN echo "{ "directory": "bower_components", "allow_root": true }" > /root/.bowerrc

WORKDIR /srv

CMD ["bash"]
