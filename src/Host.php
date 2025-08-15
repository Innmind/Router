<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\UrlTemplate\Template;
use Innmind\Immutable\{
    Attempt,
    Map,
};

final class Host
{
    /**
     * The template must end with a `/` otherwise it will never match.
     *
     * @psalm-pure
     *
     * @param literal-string|Template $template
     *
     * @throws \Exception if the template is not a valid url template
     *
     * @return Component<mixed, Map<string, string>>
     */
    public static function of(string|Template $template): Component
    {
        if (\is_string($template)) {
            $template = Template::of($template);
        }

        return Component::of(
            static fn($request, $input) => Attempt::of(
                static function() use ($request, $template) {
                    $url = $request
                        ->url()
                        ->withoutScheme()
                        ->withAuthority(
                            $request
                                ->url()
                                ->authority()
                                ->withoutUserInformation()
                                ->withoutPort(),
                        )
                        ->withoutPath()
                        ->withoutQuery()
                        ->withoutFragment();

                    return match ($template->matches($url)) {
                        true => $template->extract($url),
                        false => throw new Exception\NotFound,
                    };
                },
            ),
        );
    }
}
