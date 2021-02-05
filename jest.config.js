module.exports = {
	moduleNameMapper: {
		"\\.(jpg|jpeg|png|gif|eot|otf|webp|svg|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga|ico)$":
			"<rootDir>/resources/assets/test/__mocks__/file-mock.js",
	},
	setupFiles: ["<rootDir>/resources/assets/test/setup.js"],
};
