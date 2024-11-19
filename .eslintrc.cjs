module.exports = {
  root: true,
  globals: {
    NodeJS: true,
    JQuery: true,
    JSX: true
  },
  env: {
    node: true,
    browser: true,
    commonjs: true,
    es6: true,
    jquery: true
  },
  plugins: [
    '@typescript-eslint',
    'jquery'
  ],
  extends: [
    'eslint:recommended',
    'plugin:jquery/deprecated',
    'plugin:@typescript-eslint/eslint-recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/vue3-strongly-recommended',
    'plugin:vue/vue3-essential',
    'plugin:vue/vue3-recommended',
    'prettier'
  ],
  parserOptions: {
    parser: '@typescript-eslint/parser',
    ecmaFeatures: {
      jsx: true
    }
  },
  rules: {
    '@typescript-eslint/consistent-type-definitions': [
      'error',
      'interface'
    ],
    '@typescript-eslint/no-explicit-any': 'off',
    '@typescript-eslint/no-namespace': ['error', { allowDeclarations: true }],
    '@typescript-eslint/no-unused-vars': 'error',
    'constructor-super': 'off',
    'import/extensions': 'off',
    'import/prefer-default-export': 'off',
    'lines-between-class-members': ['error', 'always', { exceptAfterSingleLine: true }],
    'no-bitwise': ['error', { allow: ['~'], int32Hint: true }],
    'no-console': 'error',
    'no-debugger': 'error',
    'no-param-reassign': 'off',
    'no-plusplus': 'off',
    'no-unused-expressions': 'error',
    'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
    'prefer-destructuring': ['error', { 'array': true, 'object': true }, { 'enforceForRenamedProperties': false }],
    'vue/component-definition-name-casing': ['error', 'PascalCase'],
    'vue/component-name-in-template-casing': ['error', 'PascalCase', {
      'registeredComponentsOnly': true
    }],
    'vue/match-component-file-name': ['error', {
      'extensions': ['vue'],
      'shouldMatchCase': false
    }],
    'vue/no-dupe-keys': [`error`, {
      'groups': []
    }],
    'vue/no-irregular-whitespace': ['error', {
      'skipStrings': true,
      'skipComments': false,
      'skipRegExps': false,
      'skipTemplates': false,
      'skipHTMLAttributeValues': false,
      'skipHTMLTextContents': false
    }],
    'vue/no-unused-vars': 'error',
    'vue/order-in-components': ['error'],
    'vue/v-on-event-hyphenation': ['error', 'always', { 'autofix': true }]
  }
};
