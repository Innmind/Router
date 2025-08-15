<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\UrlTemplate\Template;
use Innmind\Immutable\{
    Attempt,
    Map,
};

final class Endpoint
{
    /**
     * This template should only specify the path of the request url.
     *
     * @psalm-pure
     *
     * @param literal-string|Template $template
     *
     * @throws \Exception if the template is not a valid url template
     *
     * @return Component<mixed, Map<string, string>>
     */
    #[\NoDiscard]
    public static function of(string|Template|Route $template): Component
    {
        if (\is_string($template)) {
            $template = Template::of($template);
        } else if ($template instanceof Route) {
            $template = $template->template();
        }

        return Component::of(
            static fn($request, $input) => Attempt::of(
                static function() use ($request, $template) {
                    $url = $request
                        ->url()
                        ->withoutScheme()
                        ->withoutAuthority();

                    return match ($template->matches($url)) {
                        true => $template->extract($url),
                        false => throw new Exception\NotFound,
                    };
                },
            ),
        );
    }
}
