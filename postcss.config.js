// module.exports = {
//     plugins: {
//         tailwindcss: {},
//         autoprefixer: {},
//     },
// };
module.exports = {
    plugins: [
        require("postcss-import"),
        require("postcss-advanced-variables"),
        require("tailwindcss/nesting"),
        require("tailwindcss")("./tailwind.config.js"),
        require("autoprefixer"),
    ],
};

