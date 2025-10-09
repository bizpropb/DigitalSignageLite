# Filament 4 Review: Don't Bother

## (Windows) Performance (TL;DR: It's bad)
- 2+ second page loads (on windows)
- Docker bind mounts = 20+ seconds
- Tried everything, still slow
- Completely negates any benefits you would get from docker
- Deployment is a huge hassle, given it's PHP and you can't really develop in docker (no hotreload, plus see below)

## Custom Components
- View resolutions fail silently
- Property conflicts everywhere
- Alpine.js events randomly break
- Completely closed off, can't customize nearly anything without a custom component
- And why even bother if you have to go that far
- You play by fillament's rules or you don't play at all. this isn't react.
- AI hates Fillament too. It's just too closed of a system, given how imprecise AI works.
- No client side rendering either

## Custom CSS
- Trying to override styles is a nightmare
- All css is minified, so you have to overwrite the source
- No. Just no. Don't even try it. Get a different framework.

## Debugging
- No browser console support (Laravel swallows it all into the backend logs, instead of displaying stuff in the browser debugger)
    - (Livewire JSON errors are not even echoed to console)
    - same goes for websockets (good luck debugging that)
    - at least API communications are displayed
- You're pretty much blind and forced to check the log file which is buried in somewhere in /storage/logs/laravel.log (you still have to log anything that isnt an "error" yourself manually btw.)
- Error messages are delivered one by one, with little context, so you either go hot reload or go home.
- Terrible workarounds to get proper logging like "Get-Content -Tail 0 -Wait backend\storage\logs\laravel.log"
- Forced to install things like Laravel Telescope For Filament and Horizon For Filament, since Laravel Debug bar doesnt even fully work with Filament
- Especially painful when trying to do websockets.e

## Caching Hell
- Don't cache. Ever. Just don't. It doesn't even help that much with performance and it's unsafe -> Changes won't show until cache cleared
- Don't forget to clear cache after rolling back a mistake, or else have fun trying to find your nonexistent mistake.

## What Works
- Basic CRUD
- Prototypes

## What Doesn't
- Any advanced UX

## Alternatives
- Admin panels are a dime a dozen, no reason to use php and cripple yourself.
- Component libraries + anything .js
- Literally anything else

## Readme
- Check my readme.md and all the troubles this thing is to set up.

## Final Rating: 2/10
This is the most closed framework i've ever had the displeasure to work with. 
It's the pure antithesis of aesthetically pleasing, on top of allowing no creativity whatsoever.
Only works for throwaway admin panels.
Never use it for anything else.
Never again.