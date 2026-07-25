export default {
  // Enable Dark Mode using the 'dark' class
  darkMode: 'class',

  // Files Tailwind scans for CSS classes
  content: [
    "./resources/**/*.blade.php", // Blade files
    "./resources/**/*.js",        // JavaScript files
    "./resources/**/*.vue",       // Vue components
  ],

  theme: {
    extend: {
      // Custom colors
      colors: {
        brand: {
          DEFAULT: '#4f46e5', // Main brand color
          dark: '#4338ca',    // Dark version
          light: '#818cf8',   // Light version
        },

        sidebar: {
          DEFAULT: '#0f172a', // Sidebar background
          hover: '#1e293b',   // Hover color
          border: '#1e293b',  // Border color
        },
      },

      // Default font for the project
      fontFamily: {
        sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },

  // Add Tailwind plugins here if needed
  plugins: [],
}