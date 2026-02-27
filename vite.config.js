import { defineConfig } from "vite";

export default defineConfig({
  build: {
    outDir: "public/dist",
    rollupOptions: {
      input: "resources/js/app.js",
    },
  },
});
