#syntax=docker/dockerfile:1.4
# Base stage for dev and build
FROM node:26.13.1-alpine as builder_base

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

WORKDIR /srv/app

RUN npm install -g corepack && \
	corepack enable && \
	corepack prepare pnpm@9.4.0 --activate && \
	pnpm config -g set store-dir /.pnpm-store

# Next.js collects completely anonymous telemetry data about general usage.
# Learn more here: https://nextjs.org/telemetry
# Uncomment the following line in case you want to disable telemetry during dev and build.
# ENV NEXT_TELEMETRY_DISABLED 1


FROM builder_base AS deps

COPY --link pnpm-lock.yaml package.json ./
RUN pnpm i --fix-lockfile
RUN pnpm fetch

COPY --link . .
RUN pnpm install -r --offline


# Development image
FROM deps as dev

EXPOSE 3000
ENV PORT 3000

CMD ["sh", "-c", "pnpm install -r --offline; pnpm dev"]


FROM builder_base AS builder
COPY --link . .
COPY --from=deps --link /srv/app/node_modules ./node_modules

ARG NEXT_PUBLIC_ENTRYPOINT
ENV NEXT_PUBLIC_ENTRYPOINT=$NEXT_PUBLIC_ENTRYPOINT

ARG NEXT_PUBLIC_CF_TURNSTILE_SITE_KEY
ENV NEXT_PUBLIC_CF_TURNSTILE_SITE_KEY=$NEXT_PUBLIC_CF_TURNSTILE_SITE_KEY

ARG NEXT_PUBLIC_FIREBASE_API_KEY
ENV NEXT_PUBLIC_FIREBASE_API_KEY=$NEXT_PUBLIC_FIREBASE_API_KEY

ARG NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN
ENV NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN=$NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN

ARG NEXT_PUBLIC_FIREBASE_PROJECT_ID
ENV NEXT_PUBLIC_FIREBASE_PROJECT_ID=$NEXT_PUBLIC_FIREBASE_PROJECT_ID

ARG NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET
ENV NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET=$NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET

ARG NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID
ENV NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=$NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID

ARG NEXT_PUBLIC_FIREBASE_APP_ID
ENV NEXT_PUBLIC_FIREBASE_APP_ID=$NEXT_PUBLIC_FIREBASE_APP_ID

ARG NEXT_PUBLIC_VAPID_KEY
ENV NEXT_PUBLIC_VAPID_KEY=$NEXT_PUBLIC_VAPID_KEY

ARG NEXT_PUBLIC_GOOGLE_CLIENT_ID
ENV NEXT_PUBLIC_GOOGLE_CLIENT_ID=$NEXT_PUBLIC_GOOGLE_CLIENT_ID

# Sentry (optional, inlined at build time: the DSN is public by design). Empty = Sentry disabled.
ARG NEXT_PUBLIC_SENTRY_DSN
ENV NEXT_PUBLIC_SENTRY_DSN=$NEXT_PUBLIC_SENTRY_DSN

ARG NEXT_PUBLIC_SENTRY_ENVIRONMENT
ENV NEXT_PUBLIC_SENTRY_ENVIRONMENT=$NEXT_PUBLIC_SENTRY_ENVIRONMENT

ARG NEXT_PUBLIC_SENTRY_RELEASE
ENV NEXT_PUBLIC_SENTRY_RELEASE=$NEXT_PUBLIC_SENTRY_RELEASE

# Optional build secret (docker build --secret id=sentry_auth_token): source maps are uploaded
# to Sentry only when it is provided. It is never stored in the image layers.
RUN --mount=type=secret,id=sentry_auth_token \
	SENTRY_AUTH_TOKEN="$(cat /run/secrets/sentry_auth_token 2>/dev/null || true)" pnpm run build


# Production image, copy all the files and run next
FROM node:26.13.1-alpine AS prod
WORKDIR /srv/app

# Deps stage, preserve dependencies in cache as long as the lockfile isn't changed
RUN apk upgrade libcurl curl openssl openssl-dev libressl libcrypto3 libssl3

ENV NODE_ENV production
# Uncomment the following line in case you want to disable telemetry during runtime.
# ENV NEXT_TELEMETRY_DISABLED 1

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

COPY --from=builder --link /srv/app/public ./public

# Automatically leverage output traces to reduce image size
# https://nextjs.org/docs/advanced-features/output-file-tracing
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/standalone ./
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

ENV PORT 3000

CMD ["node", "server.js"]
