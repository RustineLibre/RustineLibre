const withPWA = require('next-pwa')({
  dest: 'public',
});

/** @type {import('next').NextConfig} */
const nextConfig = withPWA({
  reactStrictMode: true,
  swcMinify: true,
  output: 'standalone',
  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'storage.googleapis.com',
      },
    ],
  },
  eslint: {
    ignoreDuringBuilds: true,
    dirs: [
      'components',
      'contexts',
      'helpers',
      'hooks',
      'interfaces',
      'pages',
      'resources',
      'utils',
      'config',
      '__e2e__',
    ],
  },
});

module.exports = nextConfig;
