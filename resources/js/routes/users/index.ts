import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
export const show = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/users/{user}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
show.url = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'uuid' in args) {
        args = { user: args.uuid }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.uuid
        : args.user,
    }

    return show.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
show.get = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
show.head = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
const showForm = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
showForm.get = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::show
* @see app/Http/Controllers/ProfileController.php:14
* @route '/users/{user}'
*/
showForm.head = (args: { user: string | { uuid: string } } | [user: string | { uuid: string } ] | string | { uuid: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
export const courseProgress = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: courseProgress.url(args, options),
    method: 'get',
})

courseProgress.definition = {
    methods: ["get","head"],
    url: '/users/{user}/progress/{course}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
courseProgress.url = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            user: args[0],
            course: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.uuid
        : args.user,
        course: typeof args.course === 'object'
        ? args.course.slug
        : args.course,
    }

    return courseProgress.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace('{course}', parsedArgs.course.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
courseProgress.get = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: courseProgress.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
courseProgress.head = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: courseProgress.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
const courseProgressForm = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: courseProgress.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
courseProgressForm.get = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: courseProgress.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProfileController::courseProgress
* @see app/Http/Controllers/ProfileController.php:92
* @route '/users/{user}/progress/{course}'
*/
courseProgressForm.head = (args: { user: string | { uuid: string }, course: string | number | { slug: string | number } } | [user: string | { uuid: string }, course: string | number | { slug: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: courseProgress.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

courseProgress.form = courseProgressForm

const users = {
    show: Object.assign(show, show),
    courseProgress: Object.assign(courseProgress, courseProgress),
}

export default users