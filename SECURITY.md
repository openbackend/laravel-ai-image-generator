# Security Policy

## Reporting Security Vulnerabilities

We take security seriously. If you discover a security vulnerability within this package, please send an email to security@openbackend.dev. All security vulnerabilities will be promptly addressed.

Please **do not** create public issues for security vulnerabilities.

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.1   | ✅ Yes             |
| < 1.0   | ❌ No              |

## Security Best Practices

When using this package:

1. **API Keys**: Never commit API keys to version control
2. **Environment Variables**: Store sensitive configuration in `.env` files
3. **Content Filtering**: Enable content filtering to prevent inappropriate content
4. **Rate Limiting**: Configure appropriate rate limits
5. **Input Validation**: Always validate user inputs before processing
6. **File Storage**: Secure your storage directories properly
7. **Logging**: Be careful not to log sensitive information

## Security Features

This package includes several security features:

- **Content Filtering**: Built-in filtering for inappropriate content
- **Input Validation**: Comprehensive prompt validation
- **Rate Limiting**: Prevents API abuse
- **Secure Storage**: Safe file handling and storage
- **Error Handling**: Proper error handling without information leakage

## Responsible Disclosure

If you find a security issue:

1. Email the details to security@openbackend.dev
2. Provide a clear description of the vulnerability
3. Include steps to reproduce if possible
4. Allow reasonable time for the issue to be addressed
5. Avoid publicly disclosing the issue until it's resolved

We aim to respond to security reports within 48 hours and provide a fix within 7 days for critical issues.
