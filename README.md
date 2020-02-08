# made/blog

> TODO
- We should think about the Exception flow when throwing PostConfigurationExceptions
    - The application should not crash if a post is misconfigured
    - The misconfigured post should not appear
    - The error in the misconfigured post should be logged
    - Maybe put some validation in the configuration model and only specify the wrong property incl. value there
    and when the Exception is caught use another wrapper exception.

