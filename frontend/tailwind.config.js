const defaultTheme = require("tailwindcss/defaultTheme");

module.exports = {
  content: [
    "components/**/*.{vue,js}",
    "pages/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#F3CC20",
        dark: "#171717",
        light: "#f5f5f5",
        white: "#fff",
      },
      aspectRatio: {
        "3/4": "3 / 4",
      },
      fontFamily: {
        headline: "'DM Serif Display','Source Sans Pro', sans-serif",
      },
    },
    fontFamily: {
      sans: ["Source Sans Pro", ...defaultTheme.fontFamily.sans],
    },
    container: {
      center: true,
      padding: {
        DEFAULT: "1rem",
        sm: "2rem",
        lg: "4rem",
        xl: "5rem",
        "2xl": "6rem",
      },
    },
  },
  plugins: [require("@tailwindcss/typography")],
};
