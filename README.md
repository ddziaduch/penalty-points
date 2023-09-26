# Hi there!

This repository is part of my presentation "Framework agnostic is not a rocket science" which can be found at https://speakerdeck.com/ddziaduch/framework-agnostic-is-not-a-rocket-science

# Navigation
Navigation is done via branches. All the branches have indexes, so you can easily follow them.
1. First branch which is named `why` shows the ugly code without our architecture.
2. Next is called `domain` where the domain is distilled.
3. After that there is `application` which hold the domain wrapped with the application.
4. Finally, there is `adapters`. which is crucial part. In this branch the application is wired with the external world via primary ports, allowing to execute actions on the application.
