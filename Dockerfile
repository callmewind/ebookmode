FROM node:current-alpine AS base
RUN mkdir -p /app
EXPOSE 8080

FROM base AS development
ENV NODE_ENV=development
COPY package*.json /
RUN npm install
WORKDIR /app
CMD [ "node", "node_modules/nodemon/bin/nodemon.js" ]

FROM base AS production
ENV NODE_ENV=production
RUN chown node:node /app
RUN mkdir -p /app/node_modules
RUN chown node:node /app/node_modules
USER node
WORKDIR /app
COPY --chown=node:node . .
RUN npm install
CMD [ "node", "app.js" ]